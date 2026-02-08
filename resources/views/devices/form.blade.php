<div class="space-y-6">
    {{-- Device Information Section --}}
    <div>
        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Device Information') }}</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Device Name') }} <span class="text-red-500">*</span></label>
                <input name="name" type="text" id="name" value="{{ old('name', isset($device) ? $device->name : '') }}" placeholder="e.g. Main Gate" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="model" class="block text-sm font-medium text-gray-700">{{ __('Model') }} <span class="text-red-500">*</span></label>
                <select name="model" id="model" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('model') border-red-500 @enderror">
                    <option value="">-- Select Device Model --</option>
                    <optgroup label="Modern Devices (ADMS Protocol)">
                        <option value="ZKTeco WL10" {{ old('model', isset($device) ? $device->model : '') === 'ZKTeco WL10' ? 'selected' : '' }}>ZKTeco WL10 (WiFi Terminal)</option>
                        <option value="ZKTeco WL20" {{ old('model', isset($device) ? $device->model : '') === 'ZKTeco WL20' ? 'selected' : '' }}>ZKTeco WL20 (WiFi Terminal)</option>
                        <option value="ZKTeco WL30" {{ old('model', isset($device) ? $device->model : '') === 'ZKTeco WL30' ? 'selected' : '' }}>ZKTeco WL30 (WiFi Terminal)</option>
                        <option value="ZKTeco WL40" {{ old('model', isset($device) ? $device->model : '') === 'ZKTeco WL40' ? 'selected' : '' }}>ZKTeco WL40 (WiFi Terminal)</option>
                        <option value="ZKTeco WL50" {{ old('model', isset($device) ? $device->model : '') === 'ZKTeco WL50' ? 'selected' : '' }}>ZKTeco WL50 (WiFi Terminal)</option>
                    </optgroup>
                    <optgroup label="Legacy Devices (ZKEM Protocol)">
                        <option value="ZKTeco K21" {{ old('model', isset($device) ? $device->model : '') === 'ZKTeco K21' ? 'selected' : '' }}>ZKTeco K21 (Biometric Terminal)</option>
                        <option value="ZKTeco K40" {{ old('model', isset($device) ? $device->model : '') === 'ZKTeco K40' ? 'selected' : '' }}>ZKTeco K40 (Biometric Terminal)</option>
                        <option value="ZKTeco K50" {{ old('model', isset($device) ? $device->model : '') === 'ZKTeco K50' ? 'selected' : '' }}>ZKTeco K50 (Biometric Terminal)</option>
                        <option value="ZKTeco K60" {{ old('model', isset($device) ? $device->model : '') === 'ZKTeco K60' ? 'selected' : '' }}>ZKTeco K60 (Biometric Terminal)</option>
                        <option value="ZKTeco U100" {{ old('model', isset($device) ? $device->model : '') === 'ZKTeco U100' ? 'selected' : '' }}>ZKTeco U100 (Attendance Device)</option>
                        <option value="ZKTeco U200" {{ old('model', isset($device) ? $device->model : '') === 'ZKTeco U200' ? 'selected' : '' }}>ZKTeco U200 (Attendance Device)</option>
                        <option value="ZKTeco iClock" {{ old('model', isset($device) ? $device->model : '') === 'ZKTeco iClock' ? 'selected' : '' }}>ZKTeco iClock (Internet Terminal)</option>
                        <option value="ZKTeco LX17" {{ old('model', isset($device) ? $device->model : '') === 'ZKTeco LX17' ? 'selected' : '' }}>ZKTeco LX17 (Terminal)</option>
                        <option value="ZKTeco P160" {{ old('model', isset($device) ? $device->model : '') === 'ZKTeco P160' ? 'selected' : '' }}>ZKTeco P160 (Attendance Device)</option>
                    </optgroup>
                    <optgroup label="NGTECO (LAN)">
                        <option value="NGTECO LAN" {{ old('model', isset($device) ? $device->model : '') === 'NGTECO LAN' ? 'selected' : '' }}>NGTECO LAN (TCP/IP)</option>
                    </optgroup>
                </select>
                @error('model') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4 mt-4">
            <div>
                <label for="serial_number" class="block text-sm font-medium text-gray-700">{{ __('Serial Number') }}</label>
                <input name="serial_number" type="text" id="serial_number" value="{{ old('serial_number', isset($device) ? $device->serial_number : '') }}" placeholder="e.g. BQWD123456789" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('serial_number') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">
                    <strong>Required for ADMS/Push devices</strong> (WL10, WL20, etc.) to match incoming push logs. Find this in the device's system information.
                </p>
                @error('serial_number') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="protocol" class="block text-sm font-medium text-gray-700">{{ __('SDK Protocol') }}</label>
                <div class="mt-1 p-3 bg-blue-50 border border-blue-200 rounded-md">
                    <p id="protocolDisplay" class="text-sm font-medium text-blue-900">
                        {{ old('protocol', isset($device) ? $device->protocol : 'auto') === 'auto' || !old('protocol', isset($device) ? $device->protocol : '') ? 'Auto-detect (default)' : ($device->protocol === 'adms' ? 'ADMS Protocol' : ($device->protocol === 'zkem' ? 'ZKEM Protocol' : ($device->protocol === 'ngteco' ? 'NGTECO LAN' : 'Auto-detect'))) }}
                    </p>
                    <input type="hidden" name="protocol" id="protocol" value="{{ old('protocol', isset($device) ? $device->protocol : 'auto') }}">
                </div>
            </div>
        </div>
        <div class="mt-4">
            <label for="location" class="block text-sm font-medium text-gray-700">{{ __('Location') }}</label>
            <input name="location" type="text" id="location" value="{{ old('location', isset($device) ? $device->location : '') }}" placeholder="e.g. Front Entrance" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('location') border-red-500 @enderror">
            @error('location') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Connection Information Section --}}
    <div>
        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Connection Information') }}</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="ip_address" class="block text-sm font-medium text-gray-700">{{ __('IP Address') }} <span class="text-red-500">*</span></label>
                <input name="ip_address" type="text" id="ip_address" value="{{ old('ip_address', isset($device) ? $device->ip_address : '') }}" placeholder="192.168.1.100" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('ip_address') border-red-500 @enderror">
                @error('ip_address') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="port" class="block text-sm font-medium text-gray-700">{{ __('Port') }} <span class="text-red-500">*</span></label>
                <input name="port" type="number" id="port" min="1" max="65535" value="{{ old('port', isset($device) ? $device->port : 4370) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('port') border-red-500 @enderror">
                @error('port') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="mt-4">
            <label for="is_active" class="flex items-center">
                <input type="hidden" name="is_active" value="0">
                <input name="is_active" type="checkbox" id="is_active" value="1" {{ old('is_active', isset($device) ? $device->is_active : true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <span class="ms-2 text-sm text-gray-700">{{ __('Active') }}</span>
            </label>
        </div>

        {{-- Test Connection Button --}}
        <div class="mt-4">
            <button type="button" id="testBtn" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                <svg id="spinner" class="hidden animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ __('Test Connection') }}
            </button>
        </div>

        {{-- Test Results --}}
        <div id="testResult" class="hidden mt-3 p-3 rounded-md">
            <p id="testMessage" class="text-sm"></p>
        </div>
    </div>

    {{-- Form Actions --}}
    <div class="flex justify-between items-center pt-4 border-t border-gray-200">
        <a href="{{ route('devices.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            {{ __('Cancel') }}
        </a>
        <button type="submit" id="submitBtn" disabled class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
            {{ isset($device) ? __('Update Device') : __('Create Device') }}
        </button>
    </div>
</div>

<script>
    const testBtn = document.getElementById('testBtn');
    const submitBtn = document.getElementById('submitBtn');
    const testResult = document.getElementById('testResult');
    const testMessage = document.getElementById('testMessage');
    const spinner = document.getElementById('spinner');
    const nameInput = document.querySelector('input[name="name"]');
    const ipInput = document.querySelector('input[name="ip_address"]');
    const portInput = document.querySelector('input[name="port"]');

    // Enable submit button if device exists (edit mode)
    @if(isset($device))
        submitBtn.disabled = false;
    @endif

    testBtn.addEventListener('click', async function(e) {
        e.preventDefault();

        // Validate required fields
        if (!nameInput.value || !ipInput.value || !portInput.value) {
            testMessage.textContent = 'Please fill in Device Name, IP Address, and Port before testing.';
            testResult.className = 'mt-3 p-3 rounded-md bg-red-50 border border-red-200';
            testMessage.className = 'text-sm text-red-700';
            testResult.classList.remove('hidden');
            submitBtn.disabled = true;
            return;
        }

        testBtn.disabled = true;
        spinner.classList.remove('hidden');

        try {
            const response = await fetch(`{{ route('devices.test-connection') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify({
                    ip_address: ipInput.value,
                    port: portInput.value,
                    name: nameInput.value
                })
            });

            const data = await response.json();
            
            if (data.success) {
                testMessage.textContent = data.message;
                testResult.className = 'mt-3 p-3 rounded-md bg-green-50 border border-green-200';
                testMessage.className = 'text-sm text-green-700';
                submitBtn.disabled = false;
            } else {
                testMessage.textContent = data.message;
                testResult.className = 'mt-3 p-3 rounded-md bg-red-50 border border-red-200';
                testMessage.className = 'text-sm text-red-700';
                submitBtn.disabled = true;
            }
        } catch (error) {
            testMessage.textContent = 'Error: ' + error.message;
            testResult.className = 'mt-3 p-3 rounded-md bg-red-50 border border-red-200';
            testMessage.className = 'text-sm text-red-700';
            submitBtn.disabled = true;
        } finally {
            testBtn.disabled = false;
            spinner.classList.add('hidden');
            testResult.classList.remove('hidden');
        }
    });

    // Handle model selection and auto-assign protocol
    const modelSelect = document.getElementById('model');
    const protocolInput = document.getElementById('protocol');
    const protocolDisplay = document.getElementById('protocolDisplay');

    // Protocol mapping for device models
    const protocolMap = {
        // ADMS Protocol (Modern devices)
        'ZKTeco WL10': 'adms',
        'ZKTeco WL20': 'adms',
        'ZKTeco WL30': 'adms',
        'ZKTeco WL40': 'adms',
        'ZKTeco WL50': 'adms',
        // ZKEM Protocol (Legacy devices)
        'ZKTeco K21': 'zkem',
        'ZKTeco K40': 'zkem',
        'ZKTeco K50': 'zkem',
        'ZKTeco K60': 'zkem',
        'ZKTeco U100': 'zkem',
        'ZKTeco U200': 'zkem',
        'ZKTeco iClock': 'zkem',
        'ZKTeco LX17': 'zkem',
        'ZKTeco P160': 'zkem',
        'NGTECO LAN': 'ngteco',
    };

    modelSelect.addEventListener('change', function() {
        const selectedModel = this.value;
        const protocol = protocolMap[selectedModel] || 'auto';
        
        protocolInput.value = protocol;
        
        // Update display text
        if (protocol === 'adms') {
            protocolDisplay.textContent = 'ADMS Protocol (Modern Device)';
            protocolDisplay.classList.remove('text-blue-900');
            protocolDisplay.classList.add('text-green-900');
            protocolDisplay.parentElement.classList.remove('bg-blue-50', 'border-blue-200');
            protocolDisplay.parentElement.classList.add('bg-green-50', 'border-green-200');
        } else if (protocol === 'zkem') {
            protocolDisplay.textContent = 'ZKEM Protocol (Legacy Device)';
            protocolDisplay.classList.remove('text-blue-900');
            protocolDisplay.classList.add('text-amber-900');
            protocolDisplay.parentElement.classList.remove('bg-blue-50', 'border-blue-200');
            protocolDisplay.parentElement.classList.add('bg-amber-50', 'border-amber-200');
        } else {
            protocolDisplay.textContent = 'Auto-detect (default)';
            protocolDisplay.classList.remove('text-green-900', 'text-amber-900');
            protocolDisplay.classList.add('text-blue-900');
            protocolDisplay.parentElement.classList.remove('bg-green-50', 'bg-amber-50', 'border-green-200', 'border-amber-200');
            protocolDisplay.parentElement.classList.add('bg-blue-50', 'border-blue-200');
        }
    });

    // Initialize protocol display on page load
    if (modelSelect.value) {
        modelSelect.dispatchEvent(new Event('change'));
    }
</script>


