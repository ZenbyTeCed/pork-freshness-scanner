@extends('layouts.app')

@section('content')
<div class="scan-page">
    <div class="scan-container">
        <div class="scan-header">
            <h1>Scan Pork Sample</h1>
            <p>Capture or upload an image for AI freshness analysis</p>
        </div>

        <div class="scan-grid">
            <div class="scan-left">
                <div class="scan-card">
                    <h2>Capture Method</h2>

                    <div class="capture-methods">
                        <button type="button" class="capture-btn active" id="uploadTriggerBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V6.75m0 0-3.75 3.75M12 6.75l3.75 3.75M3 15.75v1.5A2.25 2.25 0 005.25 19.5h13.5A2.25 2.25 0 0021 17.25v-1.5" />
                            </svg>
                            <span>Upload Image</span>
                        </button>

                        <button type="button" class="capture-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.25 5.625h-1.5A2.25 2.25 0 001.5 7.875v10.5a2.25 2.25 0 002.25 2.25h16.5a2.25 2.25 0 002.25-2.25V7.875a2.25 2.25 0 00-2.25-2.25h-1.5a2.31 2.31 0 01-1.577-.55l-1.298-1.24A2.25 2.25 0 0014.323 3h-4.646a2.25 2.25 0 00-1.552.635l-1.298 1.24z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 11.25a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                            </svg>
                            <span>ESP32-CAM</span>
                        </button>
                    </div>
                </div>

                <div class="scan-card upload-card">
                    <input type="file" id="scanImage" accept="image/png,image/jpeg,image/jpg" hidden>

                    <div class="upload-dropzone" id="uploadDropzone" tabindex="0" role="button">
                        <div class="upload-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 013.182 0L15.75 15.75m-1.5-1.5 1.409-1.409a2.25 2.25 0 013.182 0L21.75 15.75M3.75 19.5h16.5A1.5 1.5 0 0021.75 18V6A1.5 1.5 0 0020.25 4.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5Zm11.25-10.125h.008v.008H15v-.008Z" />
                            </svg>
                        </div>

                        <h3>Click to upload image</h3>
                        <p>or drag and drop</p>
                        <small>PNG, JPG up to 10MB</small>
                    </div>
                </div>

                <div class="scan-card guidelines-card">
                    <h2>Capture Guidelines</h2>
                    <ul>
                        <li>Ensure good lighting without shadows</li>
                        <li>Capture the entire meat surface</li>
                        <li>Avoid reflections and glare</li>
                        <li>Keep camera stable and focused</li>
                    </ul>
                </div>
            </div>

            <div class="scan-right">
                <div class="scan-card">
                    <div class="preview-header">
                        <h2>Preview</h2>

                        <button type="button" class="remove-preview-btn" id="removePreviewBtn" aria-label="Remove image">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="preview-box">
                        <img id="previewImage" src="" alt="Preview" class="preview-image">

                        <div id="previewPlaceholder" class="preview-placeholder">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 013.182 0L15.75 15.75m-1.5-1.5 1.409-1.409a2.25 2.25 0 013.182 0L21.75 15.75M3.75 19.5h16.5A1.5 1.5 0 0021.75 18V6A1.5 1.5 0 0020.25 4.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5Zm11.25-10.125h.008v.008H15v-.008Z" />
                            </svg>
                            <p>No image selected</p>
                        </div>
                    </div>
                </div>

                <div class="scan-card analysis-card">
                    <h2>How Analysis Works</h2>
                    <p>Our AI system evaluates multiple visual indicators including:</p>

                    <ul>
                        <li>Color consistency and tone</li>
                        <li>Surface moisture levels</li>
                        <li>Texture and firmness appearance</li>
                        <li>Signs of discoloration</li>
                    </ul>

                    <p class="analysis-note">
                        Results are provided in seconds with a confidence score and detailed recommendations.
                    </p>

                    <button type="button" class="submit-scan-btn" id="submitScanBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <span>Analyze Image</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const scanImageInput = document.getElementById('scanImage');
    const previewImage = document.getElementById('previewImage');
    const previewPlaceholder = document.getElementById('previewPlaceholder');
    const uploadTriggerBtn = document.getElementById('uploadTriggerBtn');
    const uploadDropzone = document.getElementById('uploadDropzone');
    const removePreviewBtn = document.getElementById('removePreviewBtn');

    function resetPreview() {
        previewImage.src = '';
        previewImage.style.display = 'none';
        previewPlaceholder.style.display = 'flex';
        removePreviewBtn.style.display = 'none';
        scanImageInput.value = '';
    }

    function showPreview(file) {
        if (!file) {
            resetPreview();
            return;
        }

        const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
        const maxSize = 10 * 1024 * 1024;

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

        const reader = new FileReader();

        reader.onload = function (e) {
            previewImage.src = e.target.result;
            previewImage.style.display = 'block';
            previewPlaceholder.style.display = 'none';
            removePreviewBtn.style.display = 'inline-flex';
        };

        reader.readAsDataURL(file);
    }

    if (uploadTriggerBtn) {
        uploadTriggerBtn.addEventListener('click', function () {
            scanImageInput.click();
        });
    }

    if (uploadDropzone) {
        uploadDropzone.addEventListener('click', function () {
            scanImageInput.click();
        });

        uploadDropzone.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                scanImageInput.click();
            }
        });

        ['dragenter', 'dragover'].forEach(function (eventName) {
            uploadDropzone.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                uploadDropzone.classList.add('dragover');
            });
        });

        ['dragleave', 'dragend'].forEach(function (eventName) {
            uploadDropzone.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
                uploadDropzone.classList.remove('dragover');
            });
        });

        uploadDropzone.addEventListener('drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
            uploadDropzone.classList.remove('dragover');

            const file = e.dataTransfer.files[0];
            showPreview(file);
        });
    }

    if (scanImageInput) {
        scanImageInput.addEventListener('change', function (event) {
            const file = event.target.files[0];
            showPreview(file);
        });
    }

    if (removePreviewBtn) {
        removePreviewBtn.addEventListener('click', function () {
            resetPreview();
        });
    }

    // Submit scan logic
    const submitScanBtn = document.getElementById('submitScanBtn');

    if (submitScanBtn) {
        submitScanBtn.addEventListener('click', async function () {
            if (!scanImageInput.files[0]) {
                alert('Please select an image first.');
                return;
            }

            // Show loading state
            submitScanBtn.disabled = true;
            submitScanBtn.innerHTML = '<span>Analyzing...</span>';

            const formData = new FormData();
            formData.append('image', scanImageInput.files[0]);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            try {
                const response = await axios.post('/api/scan', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });

                // Redirect to result page with scan_id
                window.location.href = `/result?scan_id=${response.data.scan_id}`;
            } catch (error) {
                alert('Error submitting scan: ' + (error.response?.data?.message || 'Unknown error'));
                submitScanBtn.disabled = false;
                submitScanBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <span>Analyze Image</span>
                `;
            }
        });
    }

    resetPreview();
</script>
@endsection