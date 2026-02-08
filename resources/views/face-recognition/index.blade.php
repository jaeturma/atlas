<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">{{ __('Face Recognition') }}</h2>
                <p class="text-sm text-gray-600">Enroll faces using the local camera stream.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-xs font-semibold rounded-md hover:bg-gray-200">Back</a>
                <a href="{{ route('face-recognition.capture') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-md hover:bg-blue-700">Capture</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-green-800">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4 text-red-800">{{ session('error') }}</div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white shadow-sm rounded-lg border border-gray-200 p-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Live Camera</h3>
                    <div class="aspect-video bg-black rounded-lg overflow-hidden">
                        <video id="camera" autoplay playsinline class="w-full h-full object-cover"></video>
                    </div>
                    <div class="mt-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <p id="camera-status" class="text-sm text-gray-600">Camera is starting. If prompted, please allow access.</p>
                        <button id="camera-retry" type="button" onclick="startCamera()" class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                            Enable Camera
                        </button>
                    </div>
                    <canvas id="canvas" class="hidden"></canvas>
                </div>

                <div class="space-y-6">
                    <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Enroll & Manage</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                                <input id="employee-search" type="text" placeholder="Search employee..." class="w-full border rounded-md px-3 py-2 text-sm mb-2">
                                <select id="employee-select" class="w-full border rounded-md p-2">
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->last_name }}, {{ $emp->first_name }} ({{ $emp->badge_number ?? 'No badge' }})
                                        </option>
                                    @endforeach
                                </select>
                                <p id="enroll-count" class="text-xs text-gray-500 mt-1">Loading enrollment info...</p>
                            </div>

                            <form id="enroll-form" method="POST" action="{{ route('face-recognition.enroll') }}" class="space-y-2">
                                @csrf
                                <input type="hidden" name="employee_id" id="enroll-employee">
                                <input type="hidden" name="image" id="enroll-image">
                            </form>

                            <form id="clear-form" method="POST" action="{{ route('face-recognition.clear') }}" onsubmit="return confirm('Clear enrollment for the selected employee? This will remove stored face samples.');">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="employee_id" id="clear-employee">
                            </form>

                            <div class="flex gap-2">
                                <button id="enroll-submit" type="button" onclick="captureAndSubmit('enroll')" class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Capture & Enroll</button>
                                <button type="submit" form="clear-form" class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Clear Enrollment</button>
                            </div>

                            <div class="flex gap-2">
                                <button type="button" onclick="clearEnrollImage()" class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">Clear Image</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const video = document.getElementById('camera');
        const canvas = document.getElementById('canvas');
        const cameraStatus = document.getElementById('camera-status');
        const cameraRetry = document.getElementById('camera-retry');
        let activeStream = null;

        async function startCamera() {
            try {
                cameraStatus.textContent = 'Requesting camera permission…';
                cameraStatus.className = 'text-sm text-blue-600';
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    cameraStatus.textContent = 'Camera API not available. Use a modern browser (Chrome/Edge) over HTTPS or localhost.';
                    cameraStatus.className = 'text-sm text-red-700';
                    return;
                }

                // Stop any previous stream before starting a new one
                if (activeStream) {
                    activeStream.getTracks().forEach(t => t.stop());
                }

                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 } } });
                activeStream = stream;
                video.srcObject = stream;
                video.play().catch(e => console.warn('Video play warning:', e));
                cameraStatus.textContent = '✓ Camera is active. Ready to capture.';
                cameraStatus.className = 'text-sm text-green-700';
            } catch (err) {
                let message = 'Unable to access the camera. Please allow permission and click "Enable Camera" to retry.';
                if (err.name === 'NotAllowedError') {
                    message = 'Camera permission denied. Allow camera access in your browser (Site Settings) then click "Enable Camera".';
                } else if (err.name === 'NotFoundError') {
                    message = 'No camera detected. Connect a camera and click "Enable Camera" to retry.';
                } else if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost') {
                    message = 'Camera requires HTTPS (or localhost). Switch to HTTPS and retry.';
                }
                cameraStatus.textContent = message;
                cameraStatus.className = 'text-sm text-red-700';
                console.error(err);
            }
        }

        function setEnrollLoading(isLoading) {
            const button = document.getElementById('enroll-submit');
            if (!button) return;
            if (isLoading) {
                button.disabled = true;
                button.classList.add('opacity-75', 'cursor-not-allowed');
                button.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    Enrolling...
                `;
            } else {
                button.disabled = false;
                button.classList.remove('opacity-75', 'cursor-not-allowed');
                button.textContent = 'Capture & Enroll';
            }
        }

        function captureAndSubmit(mode) {
            syncSelectedEmployee();
            if (mode === 'enroll') {
                setEnrollLoading(true);
            }
            const context = canvas.getContext('2d');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            const dataUrl = canvas.toDataURL('image/png');

            if (mode === 'enroll') {
                document.getElementById('enroll-image').value = dataUrl;
                document.getElementById('enroll-form').submit();
            }
        }

        function clearEnrollImage() {
            document.getElementById('enroll-image').value = '';
            // Provide subtle feedback
            cameraStatus.textContent = 'Enroll image cleared. Capture again to enroll.';
            cameraStatus.className = 'text-sm text-gray-600';
        }

        function syncSelectedEmployee() {
            const select = document.getElementById('employee-select');
            const enrollInput = document.getElementById('enroll-employee');
            const clearInput = document.getElementById('clear-employee');
            if (!select) return;
            const value = select.value || '';
            enrollInput.value = value;
            clearInput.value = value;
            if (value) {
                localStorage.setItem('faceRecognitionEmployee', value);
            }
        }

        function restoreSelectedEmployee() {
            const select = document.getElementById('employee-select');
            if (!select) return;
            const stored = localStorage.getItem('faceRecognitionEmployee');
            if (stored && select.querySelector(`option[value="${stored}"]`)) {
                select.value = stored;
            }
            syncSelectedEmployee();
        }

        function filterEmployeeOptions() {
            const search = document.getElementById('employee-search');
            const select = document.getElementById('employee-select');
            if (!search || !select) return;
            const term = search.value.toLowerCase();
            const options = Array.from(select.options);
            options.forEach(option => {
                const text = option.text.toLowerCase();
                option.hidden = term && !text.includes(term);
            });

            const visible = options.find(o => !o.hidden);
            if (visible && select.selectedOptions[0]?.hidden) {
                select.value = visible.value;
            }
            syncSelectedEmployee();
            updateEnrollmentCount();
        }

        async function updateEnrollmentCount() {
            const select = document.getElementById('employee-select');
            const countEl = document.getElementById('enroll-count');
            if (!select || !countEl) return;
            const employeeId = select.value;
            
            if (!employeeId) {
                countEl.textContent = 'No employee selected';
                countEl.className = 'text-xs text-gray-500 mt-1';
                return;
            }
            
            countEl.textContent = 'Loading...';
            countEl.className = 'text-xs text-gray-500 mt-1';
            
            try {
                const response = await fetch(`http://localhost:5000/embeddings/${employeeId}?t=${Date.now()}`, {
                    cache: 'no-cache'
                });
                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.count > 0) {
                        countEl.textContent = `✓ ${data.count} sample${data.count !== 1 ? 's' : ''} enrolled`;
                        countEl.className = 'text-xs text-green-600 mt-1 font-medium';
                    } else {
                        countEl.textContent = '⚠ No samples enrolled yet';
                        countEl.className = 'text-xs text-orange-600 mt-1';
                    }
                } else {
                    countEl.textContent = '⚠ No samples enrolled yet';
                    countEl.className = 'text-xs text-orange-600 mt-1';
                }
            } catch (err) {
                countEl.textContent = '⚠ Python backend unavailable';
                countEl.className = 'text-xs text-red-600 mt-1';
                console.warn('Failed to fetch enrollment count:', err);
            }
        }

        // Auto-start camera on page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                startCamera();
                restoreSelectedEmployee();
                updateEnrollmentCount();

                document.getElementById('employee-select')?.addEventListener('change', () => {
                    syncSelectedEmployee();
                    updateEnrollmentCount();
                });
                document.getElementById('employee-search')?.addEventListener('input', filterEmployeeOptions);

                const hasMessage = document.querySelector('.bg-green-50, .bg-red-50');
                if (hasMessage) {
                    setTimeout(() => {
                        updateEnrollmentCount();
                    }, 500);
                }
            });
        } else {
            startCamera();
            restoreSelectedEmployee();
            updateEnrollmentCount();

            document.getElementById('employee-select')?.addEventListener('change', () => {
                syncSelectedEmployee();
                updateEnrollmentCount();
            });
            document.getElementById('employee-search')?.addEventListener('input', filterEmployeeOptions);

            const hasMessage = document.querySelector('.bg-green-50, .bg-red-50');
            if (hasMessage) {
                setTimeout(() => {
                    updateEnrollmentCount();
                }, 500);
            }
        }
    </script>
</x-admin-layout>
