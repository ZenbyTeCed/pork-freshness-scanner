@extends('layouts.app')

@section('content')
{{-- Scan capture page --}}
<div class="scan-page">
    <div class="scan-container">
        <div class="scan-header">
            <h1>Scan Pork Sample</h1>
            <p>Capture or upload an image for AI freshness analysis</p>
        </div>

        {{-- Capture and preview panels --}}
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

                        <button type="button" class="capture-btn" id="cameraTriggerBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.25 5.625h-1.5A2.25 2.25 0 0 0 1.5 7.875v10.5a2.25 2.25 0 0 0 2.25 2.25h16.5a2.25 2.25 0 0 0 2.25-2.25V7.875a2.25 2.25 0 0 0-2.25-2.25h-1.5a2.31 2.31 0 0 1-1.577-.55l-1.298-1.24A2.25 2.25 0 0 0 14.323 3h-4.646a2.25 2.25 0 0 0-1.552.635l-1.298 1.24Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 11.25a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" />
                            </svg>
                            <span>Device Camera</span>
                        </button>

                        <button type="button" class="capture-btn" id="esp32CaptureBtn">
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

                <div class="scan-card camera-card" id="cameraCard" hidden>
                    <h2>Camera Capture</h2>

                    <div class="camera-preview-wrap">
                        <video id="cameraPreview" class="camera-preview" autoplay playsinline muted></video>
                        <canvas id="cameraCanvas" hidden></canvas>
                    </div>

                    <p id="cameraMessage" class="camera-message">
                        Open the camera, frame the pork sample, then take a photo.
                    </p>

                    <div class="camera-actions">
                        <button type="button" class="camera-action-btn primary" id="startCameraBtn">Open Camera</button>
                        <button type="button" class="camera-action-btn primary" id="takePhotoBtn" disabled>Take Photo</button>
                        <button type="button" class="camera-action-btn secondary" id="stopCameraBtn" disabled>Stop</button>
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
                    <h2>Browser Classification</h2>
                    <p>The Edge Impulse WebAssembly model runs locally in this browser.</p>

                    <ul>
                        <li>No backend upload is used for classification</li>
                        <li>Accepted labels: fresh, not_fresh</li>
                        <li>The image is resized before it is sent to the model</li>
                    </ul>

                    <p class="analysis-note">
                        Results are shown with the top prediction and confidence score.
                    </p>

                    <button type="button" class="submit-scan-btn" id="submitScanBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <span>Analyze Image</span>
                    </button>

                    <p id="scanStatus" class="scan-status" aria-live="polite"></p>
                </div>

                <div class="scan-card result-card">
                    <h2>Result</h2>

                    <div id="loadingState" class="loading-state" style="display:none;" aria-live="polite">
                        <div class="spinner"></div>
                        <p id="loadingStateText">Analyzing image using AI<span class="loading-dots"></span></p>
                    </div>

                    <div id="scanResult" class="scan-result">
                        <strong id="scanResultLabel">No prediction yet</strong>
                        <span id="scanResultConfidence">Select an image, then classify it in your browser.</span>
                    </div>

                    <ul id="scanResultDetails" class="result-details"></ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ESP32 capture confirmation modal --}}
<div class="esp32-modal" id="esp32ReadyModal" hidden>
    <div class="esp32-modal-backdrop" data-esp32-close></div>

    <div class="esp32-modal-panel" role="dialog" aria-modal="true" aria-labelledby="esp32ReadyTitle">
        <button type="button" class="esp32-modal-close" id="esp32ModalCloseBtn" aria-label="Close ESP32 ready dialog">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="esp32-modal-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.25 5.625h-1.5A2.25 2.25 0 001.5 7.875v10.5a2.25 2.25 0 002.25 2.25h16.5a2.25 2.25 0 002.25-2.25V7.875a2.25 2.25 0 00-2.25-2.25h-1.5a2.31 2.31 0 01-1.577-.55l-1.298-1.24A2.25 2.25 0 0014.323 3h-4.646a2.25 2.25 0 00-1.552.635l-1.298 1.24z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 11.25a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
            </svg>
        </div>

        <h2 id="esp32ReadyTitle">Ready the ESP32-CAM</h2>
        <p>Place the pork sample in front of the ESP32-CAM, keep the device powered, and make sure the view is clear before starting capture.</p>

        <div class="esp32-modal-checklist">
            <span>Device powered on</span>
            <span>Sample is framed</span>
            <span>Lighting is steady</span>
        </div>

        <div class="esp32-modal-actions">
            <button type="button" class="esp32-modal-btn secondary" id="esp32ModalCancelBtn">Cancel</button>
            <button type="button" class="esp32-modal-btn primary" id="esp32ReadyCaptureBtn">Ready to Capture</button>
        </div>
    </div>
</div>

<script>
    // Provides Edge Impulse and scan route settings.
    window.Module = {
        locateFile(path) {
            return path.endsWith('.wasm')
                ? "/js/edge-impulse/edge-impulse-standalone.wasm"
                : "/js/edge-impulse/" + path;
        }
    };
    window.scanRoutes = {
        uploadImage: "{{ route('upload.image') }}",
        captureEsp32: "{{ route('capture.esp32') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>
<script src="/js/edge-impulse/edge-impulse-standalone.js"></script>
<script src="/js/edge-impulse/run-impulse.js"></script>
<script src="/js/edge-impulse/scan-classifier.js"></script>
@endsection
