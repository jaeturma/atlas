<x-admin-layout :hide-sidebar="true" :hide-topbar="true">
    <div class="h-screen bg-gray-50 overflow-hidden">
        <div class="h-full w-full px-4 sm:px-6 lg:px-8 py-4 flex flex-col overflow-hidden">
            @if(session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4 text-red-800">{{ session('error') }}</div>
            @endif

            <div class="flex gap-4 flex-1 min-h-0 w-full">
                <!-- Left Column: Camera -->
                <div class="w-3/4 min-w-0 grid grid-rows-[minmax(0,1fr)_auto_auto] gap-4 min-h-0">
                    <!-- Camera Card -->
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 flex flex-col min-h-0">
                        <div class="flex items-center justify-between mb-3 min-h-[28px]">
                            <h3 class="text-lg font-semibold text-gray-900">Live Camera</h3>
                            <div class="flex items-center gap-3 text-right max-w-[55%]">
                                <span id="confidence-status" class="text-sm text-emerald-700 whitespace-nowrap overflow-hidden text-ellipsis"></span>
                                <p id="camera-status" class="text-sm text-gray-600 whitespace-nowrap overflow-hidden text-ellipsis">Camera is starting. If prompted, please allow access.</p>
                            </div>
                        </div>
                        <div class="bg-black rounded-lg overflow-hidden relative flex-1 min-h-0">
                            <video id="camera" autoplay playsinline class="w-full h-full object-contain"></video>
                            <canvas id="overlay" class="absolute top-0 left-0 w-full h-full pointer-events-none"></canvas>
                        </div>
                        <canvas id="canvas" class="hidden"></canvas>
                    </div>

                    <!-- Recognition Alert -->
                    <div id="recognition-alert" class="hidden bg-gray-900 text-white rounded-lg p-4 transition-all duration-300">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 id="alert-name" class="font-bold text-lg mb-1"></h4>
                                <p id="alert-badge" class="text-sm opacity-90 mb-1"></p>
                                <div class="flex items-center gap-3 text-xs">
                                    <span id="alert-confidence"></span>
                                    <span id="alert-timestamp"></span>
                                </div>
                            </div>
                            <button onclick="closeAlert()" class="text-white opacity-75 hover:opacity-100">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Instructions below camera -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-blue-900 mb-2">Instructions</h3>
                        <ol class="list-decimal list-inside text-sm text-blue-800 space-y-1">
                            <li>First, ensure employees are enrolled in the system.</li>
                            <li>Position your face in the center of the camera and ensure good lighting for accurate detection.</li>
                        </ol>
                    </div>
                </div>

                <!-- Right Column: Recognition History -->
                <div class="w-1/4 min-w-0 flex flex-col min-h-0">
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 flex flex-col flex-1 min-h-0">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Recognition History</h3>
                        <div id="recognition-history" class="space-y-2 flex-1 min-h-0 overflow-y-auto">
                            <p class="text-sm text-gray-500 text-center py-8">No recognitions yet</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script>
        // Fallback loader if CDN is blocked
        (function loadFaceApiFallback() {
            if (window.faceapi) return;
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/face-api.js@0.22.2/dist/face-api.min.js';
            script.onload = () => console.log('✓ face-api.js loaded from unpkg');
            script.onerror = () => console.warn('✗ face-api.js failed to load from unpkg');
            document.head.appendChild(script);
        })();
    </script>
    <script>
        const video = document.getElementById('camera');
        const canvas = document.getElementById('canvas');
        const overlay = document.getElementById('overlay');
        const overlayCtx = overlay.getContext('2d');
        const cameraStatus = document.getElementById('camera-status');
        const detectionStatus = document.getElementById('detection-status') || { textContent: '', className: '', innerHTML: '' };
        const fpsCounter = document.getElementById('fps-counter') || { textContent: '' };
        const resultModal = document.getElementById('result-modal');
        const modalContent = document.getElementById('modal-content');
        
        let activeStream = null;
        let lastCaptureTime = 0;
        const CAPTURE_COOLDOWN = 1500; // 1.5 seconds between detections
        const MAX_CAPTURE_WIDTH = 640;
        let frameCount = 0;
        let lastFpsTime = Date.now();
        let allEmployees = [];
        let lastRecognizedName = '';
        let lastRecognizedTime = '';
        let lastConfidence = null;
        let lastConfidenceTime = 0;
        let isRecognizing = false;
        const MIN_HISTORY_CONFIDENCE = 70;

        // Resize overlay canvas to match video
        function resizeOverlay() {
            overlay.width = video.videoWidth;
            overlay.height = video.videoHeight;
        }

        video.addEventListener('loadedmetadata', resizeOverlay);
        // Add recognition to history list
        function addRecognitionToHistory(data) {
            const historyContainer = document.getElementById('recognition-history');
            
            // Remove "no recognitions" message if it exists
            const emptyMessage = historyContainer.querySelector('p.text-gray-500');
            if (emptyMessage) {
                emptyMessage.remove();
            }
            
            // Create new entry
            const entry = document.createElement('div');
            entry.className = 'border border-gray-200 rounded-lg p-3 transition-all duration-300 bg-blue-900 text-white';
            entry.innerHTML = `
                <div class="flex items-start justify-between mb-2">
                    <h4 class="font-semibold text-sm text-white">${data.name}</h4>
                    <span class="text-xs px-2 py-0.5 rounded-full ${
                        data.confidence >= 80 ? 'bg-green-600 text-white' : 
                        data.confidence >= 60 ? 'bg-yellow-600 text-white' : 
                        'bg-orange-600 text-white'
                    }">${data.confidence.toFixed(1)}%</span>
                </div>
                <div class="space-y-1">
                    <p class="text-xs text-blue-100"><span class="font-medium">Badge:</span> ${data.badge}</p>
                    <p class="text-xs text-blue-200">${data.timestamp}</p>
                </div>
            `;
            
            // Add to top of list
            historyContainer.insertBefore(entry, historyContainer.firstChild);
            
            // Animate: dark bg -> light bg after 1 second, then fade after 3 seconds
            setTimeout(() => {
                entry.className = 'border border-gray-200 rounded-lg p-3 transition-all duration-1000 bg-white text-gray-900 hover:bg-gray-50';
                entry.innerHTML = `
                    <div class="flex items-start justify-between mb-2">
                        <h4 class="font-semibold text-gray-900 text-sm">${data.name}</h4>
                        <span class="text-xs px-2 py-0.5 rounded-full ${
                            data.confidence >= 80 ? 'bg-green-100 text-green-800' : 
                            data.confidence >= 60 ? 'bg-yellow-100 text-yellow-800' : 
                            'bg-orange-100 text-orange-800'
                        }">${data.confidence.toFixed(1)}%</span>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs text-gray-600"><span class="font-medium">Badge:</span> ${data.badge}</p>
                        <p class="text-xs text-gray-500">${data.timestamp}</p>
                    </div>
                `;
            }, 1000);
        }


        async function startCamera() {
            try {
                cameraStatus.textContent = 'Requesting camera permission…';
                cameraStatus.className = 'text-sm text-blue-600';

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    cameraStatus.textContent = 'Camera API not available. Use Chrome/Edge over HTTPS or localhost.';
                    cameraStatus.className = 'text-sm text-red-700';
                    return;
                }

                if (activeStream) {
                    activeStream.getTracks().forEach(t => t.stop());
                }

                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 } } 
                });
                activeStream = stream;
                video.srcObject = stream;
                await video.play();

                const track = stream.getVideoTracks()[0];
                if (track && track.getCapabilities) {
                    const caps = track.getCapabilities();
                    if (caps.zoom) {
                        track.applyConstraints({ advanced: [{ zoom: 1 }] });
                    }
                }
                
                cameraStatus.textContent = '✓ Camera active. Loading face detection models...';
                cameraStatus.className = 'text-sm text-blue-600';

                // Load employees and start detection
                await loadEmployees();
                
                try {
                    await loadFaceApiModels();
                    cameraStatus.textContent = '✓ Ready. Detecting faces...';
                    cameraStatus.className = 'text-sm text-green-700';
                    detectFaces();
                } catch (modelErr) {
                    console.warn('Face-api.js models failed to load, skipping face detection overlay:', modelErr);
                    cameraStatus.textContent = 'Camera active. Recognition running (overlay unavailable).';
                    cameraStatus.className = 'text-sm text-blue-600';
                    // Still start detection loop without face-api.js overlay
                    startBasicDetection();
                }
            } catch (err) {
                let message = 'Unable to access camera. Please allow permission.';
                if (err.name === 'NotAllowedError') {
                    message = 'Camera permission denied. Allow in browser Site Settings.';
                } else if (err.name === 'NotFoundError') {
                    message = 'No camera detected. Connect a camera and reload.';
                } else if (err.name === 'NotReadableError') {
                    message = 'Camera in use by another application. Close other apps and reload.';
                }
                cameraStatus.textContent = message;
                cameraStatus.className = 'text-sm text-red-700';
                console.error('Camera error:', err);
            }
        }

        async function loadEmployees() {
            try {
                const response = await fetch('/api/employees', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    }
                });
                const data = await response.json();
                allEmployees = data.employees || [];
            } catch (err) {
                console.warn('Could not load employees:', err);
            }
        }

        async function loadFaceApiModels() {
            const MODEL_URL = '/face-api/weights/';
            console.log('Loading face-api.js models from:', MODEL_URL);

            await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);

            console.log('✓ Face-api.js models loaded successfully');
        }

        async function detectFaces() {
            frameCount++;
            const now = Date.now();

            // Update FPS counter
            if (now - lastFpsTime >= 1000) {
                fpsCounter.textContent = `FPS: ${frameCount}`;
                frameCount = 0;
                lastFpsTime = now;
            }

            try {
                const detection = await faceapi
                    .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.4 }));

                // Clear previous drawings
                overlayCtx.clearRect(0, 0, overlay.width, overlay.height);

                if (detection) {
                    // Draw green rectangle around face
                    const box = detection.box;
                    
                    overlayCtx.strokeStyle = '#10b981'; // Green
                    overlayCtx.lineWidth = 3;
                    overlayCtx.strokeRect(box.x, box.y, box.width, box.height);

                    if (lastConfidence && (now - lastConfidenceTime) < 5000) {
                        const label = `${Math.round(lastConfidence)}%`;
                        overlayCtx.font = '16px Arial';
                        overlayCtx.fillStyle = '#10b981';
                        overlayCtx.fillRect(box.x, Math.max(0, box.y - 24), 54, 20);
                        overlayCtx.fillStyle = '#ffffff';
                        overlayCtx.fillText(label, box.x + 6, Math.max(14, box.y - 9));
                    }

                    // Check if we should capture
                    if (!isRecognizing && now - lastCaptureTime > CAPTURE_COOLDOWN) {
                        detectionStatus.textContent = `✓ Face detected! Recognizing...`;
                        detectionStatus.className = 'text-sm text-blue-600';
                        lastCaptureTime = now;
                        isRecognizing = true;
                        
                        // Capture frame and send to Python backend for recognition
                        const imageData = captureFrame();
                        try {
                            await recognizeWithPython(imageData);
                        } finally {
                            isRecognizing = false;
                        }
                    } else {
                        const timeUntilNext = Math.ceil((CAPTURE_COOLDOWN - (now - lastCaptureTime)) / 1000);
                        if (lastRecognizedName) {
                            detectionStatus.innerHTML = `<strong>${lastRecognizedName}</strong><br><small>${lastRecognizedTime}</small><br>Next scan in ${timeUntilNext}s...`;
                            detectionStatus.className = 'text-sm text-green-600';
                        } else {
                            detectionStatus.textContent = `Face detected. Next scan in ${timeUntilNext}s...`;
                            detectionStatus.className = 'text-sm text-yellow-600';
                        }
                    }
                } else {
                    if (lastRecognizedName) {
                        detectionStatus.innerHTML = `Last: <strong>${lastRecognizedName}</strong><br><small>${lastRecognizedTime}</small>`;
                        detectionStatus.className = 'text-sm text-gray-600';
                    } else {
                        detectionStatus.textContent = 'Waiting for face detection...';
                        detectionStatus.className = 'text-sm text-gray-600';
                    }
                }
            } catch (err) {
                console.error('Detection error:', err);
            }

            // Continue detection loop
            requestAnimationFrame(detectFaces);
        }

        // Fallback detection without face-api.js (just sends frames to Python backend)
        function startBasicDetection() {
            let lastCapture = 0;
            const INTERVAL = 2000; // Check every 2 seconds

            function captureAndRecognize() {
                const now = Date.now();
                if (now - lastCapture > INTERVAL) {
                    lastCapture = now;
                    detectionStatus.textContent = '📷 Capturing frame for recognition...';
                    detectionStatus.className = 'text-sm text-blue-600';
                    
                    const imageData = captureFrame();
                    recognizeWithPython(imageData).then(() => {
                        if (!lastRecognizedName) {
                            detectionStatus.textContent = 'No face recognized. Will retry...';
                            detectionStatus.className = 'text-sm text-gray-600';
                        }
                    });
                }
                requestAnimationFrame(captureAndRecognize);
            }
            
            captureAndRecognize();
        }

        function captureFrame() {
            const ctx = canvas.getContext('2d');
            const scale = Math.min(1, MAX_CAPTURE_WIDTH / video.videoWidth);
            canvas.width = Math.round(video.videoWidth * scale);
            canvas.height = Math.round(video.videoHeight * scale);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            return canvas.toDataURL('image/jpeg', 0.85);
        }

        async function recognizeWithPython(imageData) {
            try {
                // Always call Laravel API to avoid mixed-content/CORS issues
                const response = await fetch('/api/face-recognition/match', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({
                        image: imageData
                    })
                });

                const result = await response.json();

                if (result.success && (result.employee || (result.matches && result.matches.length))) {
                    const timestamp = new Date().toLocaleString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: true
                    });

                    const matches = (result.matches && result.matches.length)
                        ? result.matches
                        : [result.employee];

                    matches.forEach((m, idx) => {
                        const employee = {
                            id: m.id ?? m.employee_id,
                            first_name: m.first_name || m.name?.split(' ')[0] || '',
                            last_name: m.last_name || m.name?.split(' ').slice(1).join(' ') || '',
                            badge_number: m.badge_number,
                            confidence: m.confidence
                        };

                        const fullName = `${employee.first_name} ${employee.last_name}`.trim() || 'Unknown';
                        if (idx === 0) {
                            lastRecognizedName = fullName;
                            lastRecognizedTime = timestamp;
                            lastConfidence = employee.confidence;
                            lastConfidenceTime = Date.now();
                            const confidenceStatus = document.getElementById('confidence-status');
                            if (confidenceStatus) {
                                confidenceStatus.textContent = `Confidence: ${Math.round(employee.confidence)}%`;
                            }
                            if (employee.confidence >= MIN_HISTORY_CONFIDENCE) {
                                showRecognitionAlert(employee, employee.confidence, timestamp);
                            }
                        }

                        if (employee.confidence >= MIN_HISTORY_CONFIDENCE) {
                            addRecognitionToHistory({
                                name: fullName,
                                badge: employee.badge_number || employee.id,
                                confidence: employee.confidence,
                                timestamp: timestamp
                            });
                        }
                    });
                } else {
                    showErrorAlert(result.message || 'No matching face found.');
                }
            } catch (err) {
                showErrorAlert('Recognition service unavailable. Please check the backend.');
                console.error('Recognition error:', err);
            }
        }

        function showRecognitionAlert(employee, confidence, timestamp) {
            const alert = document.getElementById('recognition-alert');
            const alertName = document.getElementById('alert-name');
            const alertBadge = document.getElementById('alert-badge');
            const alertConfidence = document.getElementById('alert-confidence');
            const alertTimestamp = document.getElementById('alert-timestamp');
            
            if (!timestamp) {
                const now = new Date();
                timestamp = now.toLocaleString('en-US', { 
                    month: '2-digit', 
                    day: '2-digit', 
                    year: 'numeric', 
                    hour: '2-digit', 
                    minute: '2-digit', 
                    second: '2-digit',
                    hour12: true 
                });
            }
            
            alertName.textContent = `✓ ${employee.first_name} ${employee.last_name}`;
            alertBadge.textContent = `Badge: ${employee.badge_number || employee.id}`;
            alertConfidence.textContent = `Confidence: ${Math.round(confidence)}%`;
            alertTimestamp.textContent = timestamp;
            
            // Show with dark background
            alert.className = 'bg-gray-900 text-white rounded-lg p-4 transition-all duration-300';
            alert.classList.remove('hidden');
            
            // Transition to light background after 1 second
            setTimeout(() => {
                alert.className = 'bg-green-50 text-green-900 border border-green-200 rounded-lg p-4 transition-all duration-1000';
            }, 1000);
            
            // Auto-hide after 3 seconds
            setTimeout(() => {
                alert.classList.add('hidden');
            }, 3000);
        }

        function showErrorAlert(message) {
            const alert = document.getElementById('recognition-alert');
            const alertName = document.getElementById('alert-name');
            const alertBadge = document.getElementById('alert-badge');
            const alertConfidence = document.getElementById('alert-confidence');
            const alertTimestamp = document.getElementById('alert-timestamp');
            
            alertName.textContent = '✗ Recognition Failed';
            alertBadge.textContent = message;
            alertConfidence.textContent = '';
            alertTimestamp.textContent = '';
            
            alert.className = 'bg-red-600 text-white rounded-lg p-4 transition-all duration-300';
            alert.classList.remove('hidden');
            
            // Auto-hide after 2 seconds
            setTimeout(() => {
                alert.classList.add('hidden');
            }, 2000);
        }

        function closeAlert() {
            document.getElementById('recognition-alert').classList.add('hidden');
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', startCamera);
        } else {
            startCamera();
        }
    </script>
</x-admin-layout>
