document.addEventListener('DOMContentLoaded', () => {
    const scanImageInput = document.getElementById('scanImage');
    const previewImage = document.getElementById('previewImage');
    const previewPlaceholder = document.getElementById('previewPlaceholder');
    const uploadTriggerBtn = document.getElementById('uploadTriggerBtn');
    const uploadDropzone = document.getElementById('uploadDropzone');
    const removePreviewBtn = document.getElementById('removePreviewBtn');
    const classifyBtn = document.getElementById('submitScanBtn');
    const esp32CaptureBtn = document.getElementById('esp32CaptureBtn');
    const loadingState = document.getElementById('loadingState');
    const resultBox = document.getElementById('scanResult');
    const resultLabel = document.getElementById('scanResultLabel');
    const resultConfidence = document.getElementById('scanResultConfidence');
    const resultDetails = document.getElementById('scanResultDetails');
    const scanStatus = document.getElementById('scanStatus');

    const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
    const maxSize = 10 * 1024 * 1024;
    const minimumLoadingTime = 1000;
    const labelClasses = {
        fresh: 'result-fresh',
        half_fresh: 'result-half-fresh',
        spoiled: 'result-spoiled',
    };

    let selectedFile = null;
    let classifier = null;
    let modelInputSize = { width: 96, height: 96 };
    let loadingStartedAt = 0;

    function setStatus(message, isError = false) {
        scanStatus.textContent = message;
        scanStatus.classList.toggle('scan-status-error', isError);
    }

    function resetResult() {
        hideLoadingState(true);
        showResultContainer();
        resultBox.className = 'scan-result';
        resultLabel.textContent = 'No prediction yet';
        resultConfidence.textContent = 'Select an image, then classify it in your browser.';
        resultDetails.innerHTML = '';
        setStatus('');
    }

    function showLoadingState() {
        loadingStartedAt = Date.now();
        loadingState.style.display = 'flex';
        resultBox.style.display = 'none';
        resultDetails.style.display = 'none';
    }

    async function hideLoadingState(skipDelay = false) {
        if (!loadingState) return;

        const elapsedTime = Date.now() - loadingStartedAt;
        const remainingDelay = Math.max(0, minimumLoadingTime - elapsedTime);

        if (!skipDelay && remainingDelay > 0) {
            await new Promise((resolve) => setTimeout(resolve, remainingDelay));
        }

        loadingState.style.display = 'none';
    }

    function showResultContainer() {
        resultBox.style.display = 'flex';
        resultDetails.style.display = 'flex';
    }

    function showResultError(message) {
        showResultContainer();
        resultBox.className = 'scan-result result-error';
        resultLabel.textContent = 'Analysis failed';
        resultConfidence.textContent = message;
        resultDetails.innerHTML = '';
    }

    function resetPreview() {
        selectedFile = null;
        previewImage.src = '';
        previewImage.style.display = 'none';
        previewPlaceholder.style.display = 'flex';
        removePreviewBtn.style.display = 'none';
        scanImageInput.value = '';
        classifyBtn.disabled = false;
        resetResult();
    }

    function showPreview(file) {
        if (!file) {
            resetPreview();
            return;
        }

        if (!allowedTypes.includes(file.type)) {
            alert('Please upload a PNG or JPG image.');
            resetPreview();
            return;
        }

        if (file.size > maxSize) {
            alert('Image must be 10MB or below.');
            resetPreview();
            return;
        }

        selectedFile = file;
        resetResult();

        const imageUrl = URL.createObjectURL(file);
        previewImage.onload = () => URL.revokeObjectURL(imageUrl);
        previewImage.src = imageUrl;
        previewImage.style.display = 'block';
        previewPlaceholder.style.display = 'none';
        removePreviewBtn.style.display = 'inline-flex';
    }

    function pickNumber(source, keys) {
        if (!source) {
            return null;
        }

        for (const key of keys) {
            if (Number.isFinite(source[key]) && source[key] > 0) {
                return source[key];
            }
        }

        return null;
    }

    async function loadClassifier() {
        if (!classifier) {
            classifier = new window.EdgeImpulseClassifier();
            setStatus('Loading model...');
            await classifier.init();

            const projectInfo = classifier.getProjectInfo();
            const properties = classifier.getProperties();

            // Edge Impulse deployments expose model metadata differently across versions.
            // These fallbacks keep the page usable if width/height are not exported.
            modelInputSize = {
                width: pickNumber(projectInfo, ['image_input_width', 'input_width', 'width']) ||
                    pickNumber(properties, ['image_input_width', 'input_width', 'width']) ||
                    96,
                height: pickNumber(projectInfo, ['image_input_height', 'input_height', 'height']) ||
                    pickNumber(properties, ['image_input_height', 'input_height', 'height']) ||
                    96,
            };
        }

        return classifier;
    }

    function imageToEdgeImpulseFeatures(image, width, height) {
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d', { willReadFrequently: true });
        const features = [];

        canvas.width = width;
        canvas.height = height;
        context.drawImage(image, 0, 0, width, height);

        const pixels = context.getImageData(0, 0, width, height).data;

        // Image models generated by Edge Impulse expect one RGB888 number per pixel.
        for (let i = 0; i < pixels.length; i += 4) {
            const red = pixels[i];
            const green = pixels[i + 1];
            const blue = pixels[i + 2];
            features.push((red << 16) + (green << 8) + blue);
        }

        return features;
    }

    function loadImage(file) {
        return new Promise((resolve, reject) => {
            const image = new Image();
            const imageUrl = URL.createObjectURL(file);

            image.onload = () => {
                URL.revokeObjectURL(imageUrl);
                resolve(image);
            };

            image.onerror = () => {
                URL.revokeObjectURL(imageUrl);
                reject(new Error('The selected image could not be read.'));
            };

            image.src = imageUrl;
        });
    }

    function renderPrediction(results) {
        const sortedResults = [...results].sort((a, b) => b.value - a.value);
        const prediction = sortedResults[0];
        const labelClass = labelClasses[prediction.label] || '';
        const confidence = Math.round(prediction.value * 100);

        resultBox.className = `scan-result ${labelClass}`;
        showResultContainer();
        resultLabel.textContent = prediction.label;
        resultConfidence.textContent = `${confidence}% confidence`;
        resultDetails.innerHTML = sortedResults
            .map((result) => {
                const score = Math.round(result.value * 100);
                return `<li><span>${result.label}</span><strong>${score}%</strong></li>`;
            })
            .join('');
    }

    function recommendationFromPrediction(label) {
        const recommendations = {
            fresh: 'Excellent quality. Safe for immediate consumption or storage.',
            half_fresh: 'Acceptable quality. Keep refrigerated and cook thoroughly.',
            spoiled: 'Poor quality indicators detected. Do not consume if spoilage is suspected.',
        };

        return recommendations[label] || 'Review the result before consumption.';
    }

    async function saveUploadResult(prediction, confidence) {
        const formData = new FormData();
        formData.append('image', selectedFile);
        formData.append('prediction', prediction.label);
        formData.append('confidence', String(Math.round(prediction.value * 10000) / 100));
        formData.append('recommendation', recommendationFromPrediction(prediction.label));

        const response = await fetch(window.scanRoutes.uploadImage, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.scanRoutes.csrfToken,
                'Accept': 'application/json',
            },
            body: formData,
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'The upload result could not be saved.');
        }

        return data.redirect_url;
    }

    async function classifySelectedImage() {
        if (!selectedFile) {
            alert('Please select an image first.');
            return;
        }

        classifyBtn.disabled = true;
        classifyBtn.querySelector('span').textContent = 'Classifying...';
        setStatus('');
        showLoadingState();

        try {
            const edgeImpulseClassifier = await loadClassifier();
            const image = await loadImage(selectedFile);
            const features = imageToEdgeImpulseFeatures(image, modelInputSize.width, modelInputSize.height);
            const result = edgeImpulseClassifier.classify(features);

            if (!result.results || result.results.length === 0) {
                throw new Error('The model returned no classification results.');
            }

            const sortedResults = [...result.results].sort((a, b) => b.value - a.value);
            const prediction = sortedResults[0];

            await hideLoadingState();
            renderPrediction(result.results);
            setStatus('Saving result to history...');

            const redirectUrl = await saveUploadResult(prediction, prediction.value);
            window.location.href = redirectUrl;
        } catch (error) {
            console.error(error);
            await hideLoadingState();
            showResultError('Analysis failed. Try again.');
            setStatus(error.message || 'Analysis failed. Try again.', true);
        } finally {
            classifyBtn.disabled = false;
            classifyBtn.querySelector('span').textContent = 'Classify';
        }
    }

    async function captureWithEsp32() {
        esp32CaptureBtn.disabled = true;
        setStatus('Sending capture command to ESP32-CAM...');

        try {
            const response = await fetch(window.scanRoutes.captureEsp32, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.scanRoutes.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ device_id: 'esp32cam_01' }),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'ESP32 capture failed.');
            }

            setStatus('ESP32 result saved. Opening result page...');
            window.location.href = data.redirect_url;
        } catch (error) {
            console.error(error);
            setStatus(error.message || 'ESP32 capture failed. Please try again.', true);
        } finally {
            esp32CaptureBtn.disabled = false;
        }
    }

    uploadTriggerBtn?.addEventListener('click', () => scanImageInput.click());
    uploadDropzone?.addEventListener('click', () => scanImageInput.click());
    uploadDropzone?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            scanImageInput.click();
        }
    });

    ['dragenter', 'dragover'].forEach((eventName) => {
        uploadDropzone?.addEventListener(eventName, (event) => {
            event.preventDefault();
            uploadDropzone.classList.add('dragover');
        });
    });

    ['dragleave', 'dragend'].forEach((eventName) => {
        uploadDropzone?.addEventListener(eventName, (event) => {
            event.preventDefault();
            uploadDropzone.classList.remove('dragover');
        });
    });

    uploadDropzone?.addEventListener('drop', (event) => {
        event.preventDefault();
        uploadDropzone.classList.remove('dragover');
        showPreview(event.dataTransfer.files[0]);
    });

    scanImageInput?.addEventListener('change', (event) => showPreview(event.target.files[0]));
    removePreviewBtn?.addEventListener('click', resetPreview);
    classifyBtn?.addEventListener('click', classifySelectedImage);
    esp32CaptureBtn?.addEventListener('click', captureWithEsp32);

    resetPreview();
});
