document.addEventListener('DOMContentLoaded', () => {
    const scanImageInput = document.getElementById('scanImage');
    const previewImage = document.getElementById('previewImage');
    const previewPlaceholder = document.getElementById('previewPlaceholder');
    const uploadTriggerBtn = document.getElementById('uploadTriggerBtn');
    const cameraTriggerBtn = document.getElementById('cameraTriggerBtn');
    const uploadDropzone = document.getElementById('uploadDropzone');
    const removePreviewBtn = document.getElementById('removePreviewBtn');
    const classifyBtn = document.getElementById('submitScanBtn');
    const esp32CaptureBtn = document.getElementById('esp32CaptureBtn');
    const cameraCard = document.getElementById('cameraCard');
    const cameraPreview = document.getElementById('cameraPreview');
    const cameraCanvas = document.getElementById('cameraCanvas');
    const cameraMessage = document.getElementById('cameraMessage');
    const startCameraBtn = document.getElementById('startCameraBtn');
    const takePhotoBtn = document.getElementById('takePhotoBtn');
    const stopCameraBtn = document.getElementById('stopCameraBtn');
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
        not_fresh: 'result-not-fresh',
    };
    const labelText = {
        fresh: 'Fresh',
        not_fresh: 'Not Fresh',
    };

    let selectedFile = null;
    let classifier = null;
    let modelInputSize = { width: 96, height: 96 };
    let loadingStartedAt = 0;
    let cameraStream = null;

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

    function setActiveCaptureButton(activeButton) {
        [uploadTriggerBtn, cameraTriggerBtn, esp32CaptureBtn].forEach((button) => {
            button?.classList.toggle('active', button === activeButton);
        });
    }

    function setCameraMessage(message, isError = false) {
        cameraMessage.textContent = message;
        cameraMessage.classList.toggle('error', isError);
    }

    function scrollToMobileTarget(element) {
        if (!window.matchMedia('(max-width: 900px)').matches || !element) {
            return;
        }

        setTimeout(() => {
            element.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
            });
        }, 100);
    }

    function showCameraCard() {
        cameraCard.hidden = false;
        setActiveCaptureButton(cameraTriggerBtn);
        scrollToMobileTarget(cameraCard);
    }

    function hideCameraCard() {
        cameraCard.hidden = true;
        stopCamera();
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

    async function startCamera() {
        showCameraCard();

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            setCameraMessage('Camera capture is not supported by this browser.', true);
            return;
        }

        try {
            stopCamera();
            setCameraMessage('Opening camera...');

            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: { ideal: 'environment' },
                },
                audio: false,
            });

            cameraPreview.srcObject = cameraStream;
            startCameraBtn.disabled = true;
            takePhotoBtn.disabled = false;
            stopCameraBtn.disabled = false;
            setCameraMessage('Camera ready. Frame the pork sample and take a photo.');
        } catch (error) {
            console.error(error);
            setCameraMessage('Camera permission was denied or no camera is available.', true);
            startCameraBtn.disabled = false;
            takePhotoBtn.disabled = true;
            stopCameraBtn.disabled = true;
        }
    }

    function stopCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach((track) => track.stop());
            cameraStream = null;
        }

        cameraPreview.srcObject = null;
        startCameraBtn.disabled = false;
        takePhotoBtn.disabled = true;
        stopCameraBtn.disabled = true;
    }

    function takePhotoFromCamera() {
        if (!cameraStream || cameraPreview.videoWidth === 0) {
            setCameraMessage('Camera is not ready yet. Please wait a moment.', true);
            return;
        }

        cameraCanvas.width = cameraPreview.videoWidth;
        cameraCanvas.height = cameraPreview.videoHeight;
        cameraCanvas.getContext('2d').drawImage(cameraPreview, 0, 0);

        cameraCanvas.toBlob((blob) => {
            if (!blob) {
                setCameraMessage('Could not capture a photo. Please try again.', true);
                return;
            }

            const file = new File([blob], `camera-capture-${Date.now()}.jpg`, {
                type: 'image/jpeg',
            });

            showPreview(file);
            setCameraMessage('Photo captured. You can now classify it.');
            scrollToMobileTarget(previewImage.closest('.scan-card'));
        }, 'image/jpeg', 0.92);
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

    function normalizeModelLabel(label) {
        const normalizedLabel = String(label || '').toLowerCase().trim().replace(/[\s-]+/g, '_');

        if (normalizedLabel === 'fresh') {
            return 'fresh';
        }

        if (normalizedLabel === 'not_fresh') {
            return 'not_fresh';
        }

        return null;
    }

    function normalizeClassificationResults(results) {
        return results
            .map((result) => ({
                label: normalizeModelLabel(result.label),
                value: Number(result.value) || 0,
            }))
            .filter((result) => result.label);
    }

    function renderPrediction(results) {
        const sortedResults = normalizeClassificationResults(results).sort((a, b) => b.value - a.value);
        const prediction = sortedResults[0];

        if (!prediction) {
            showResultError('The model did not return fresh or not_fresh.');
            return;
        }

        const labelClass = labelClasses[prediction.label] || '';
        const confidence = Math.round(prediction.value * 100);
        const message = recommendationFromPrediction(prediction.label);

        resultBox.className = `scan-result ${labelClass}`;
        showResultContainer();
        resultLabel.textContent = labelText[prediction.label] || 'Unknown';
        resultConfidence.textContent = `${confidence}% confidence. ${message}`;
        resultDetails.innerHTML = sortedResults
            .map((result) => {
                const score = Math.round(result.value * 100);
                const label = labelText[result.label] || result.label;
                return `<li><span>${label}</span><strong>${score}%</strong></li>`;
            })
            .join('');
    }

    function recommendationFromPrediction(label) {
        const recommendations = {
            fresh: 'The sample appears fresh.',
            not_fresh: 'The sample may no longer be fresh. Further checking is recommended.',
        };

        return recommendations[label] || 'Review the result before making a handling decision.';
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

        scrollToMobileTarget(resultBox.closest('.scan-card'));
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

            const sortedResults = normalizeClassificationResults(result.results).sort((a, b) => b.value - a.value);
            const prediction = sortedResults[0];

            if (!prediction) {
                throw new Error('The model did not return fresh or not_fresh.');
            }

            await hideLoadingState();
            renderPrediction(sortedResults);
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
            classifyBtn.querySelector('span').textContent = 'Analyze Image';
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

    uploadTriggerBtn?.addEventListener('click', () => {
        setActiveCaptureButton(uploadTriggerBtn);
        hideCameraCard();
        scanImageInput.click();
    });
    cameraTriggerBtn?.addEventListener('click', () => {
        showCameraCard();
        startCamera();
    });
    startCameraBtn?.addEventListener('click', startCamera);
    takePhotoBtn?.addEventListener('click', takePhotoFromCamera);
    stopCameraBtn?.addEventListener('click', () => {
        stopCamera();
        setCameraMessage('Camera stopped.');
    });
    uploadDropzone?.addEventListener('click', () => {
        setActiveCaptureButton(uploadTriggerBtn);
        hideCameraCard();
        scanImageInput.click();
    });
    uploadDropzone?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            setActiveCaptureButton(uploadTriggerBtn);
            hideCameraCard();
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
        setActiveCaptureButton(uploadTriggerBtn);
        hideCameraCard();
        uploadDropzone.classList.remove('dragover');
        showPreview(event.dataTransfer.files[0]);
    });

    scanImageInput?.addEventListener('change', (event) => {
        setActiveCaptureButton(uploadTriggerBtn);
        hideCameraCard();
        showPreview(event.target.files[0]);
    });
    removePreviewBtn?.addEventListener('click', resetPreview);
    classifyBtn?.addEventListener('click', classifySelectedImage);
    esp32CaptureBtn?.addEventListener('click', () => {
        setActiveCaptureButton(esp32CaptureBtn);
        hideCameraCard();
        captureWithEsp32();
    });
    window.addEventListener('beforeunload', stopCamera);

    resetPreview();
});
