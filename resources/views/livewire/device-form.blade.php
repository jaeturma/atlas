<div class="space-y-6">
    {{-- Device Information & Connection Section --}}
    <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Device Details') }}</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Device Name') }} <span class="text-red-500">*</span></label>
                <input name="name" wire:model.blur="name" type="text" id="name" placeholder="e.g. Main Gate" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('name') border-red-500 @enderror">
                @error('name') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="model" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Model') }}</label>
                <input name="model" wire:model.blur="model" type="text" id="model" placeholder="e.g. ZKTeco K40" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('model') border-red-500 @enderror">
                @error('model') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="serial_number" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Serial Number') }}</label>
                <input name="serial_number" wire:model.blur="serial_number" type="text" id="serial_number" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('serial_number') border-red-500 @enderror">
                @error('serial_number') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="location" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Location') }}</label>
                <input name="location" wire:model.blur="location" type="text" id="location" placeholder="e.g. Front Entrance" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('location') border-red-500 @enderror">
                @error('location') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="ip_address" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('IP Address') }} <span class="text-red-500">*</span></label>
                <input name="ip_address" wire:model.blur="ip_address" type="text" id="ip_address" placeholder="192.168.1.100" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('ip_address') border-red-500 @enderror">
                @error('ip_address') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="port" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Port') }} <span class="text-red-500">*</span></label>
                <input name="port" wire:model.blur="port" type="number" id="port" min="1" max="65535" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('port') border-red-500 @enderror">
                @error('port') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="mt-4">
            <label for="is_active" class="flex items-center">
                <input type="hidden" name="is_active" value="0">
                <input name="is_active" wire:model="is_active" type="checkbox" id="is_active" value="1" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                <span class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">{{ __('Active') }}</span>
            </label>
        </div>

        {{-- Test Connection Button --}}
        <div class="mt-6">
            <button type="button" wire:click="testConnection" wire:loading.attr="disabled" class="text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-cyan-600 dark:hover:bg-cyan-700 focus:outline-none dark:focus:ring-cyan-800 inline-flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                <svg wire:loading class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ __('Test Connection') }}
            </button>
        </div>

        {{-- Test Results --}}
        @if($testMessage)
            <div class="mt-3 p-3 rounded-md {{ $testSuccess ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                <p class="text-sm {{ $testSuccess ? 'text-green-700' : 'text-red-700' }}">
                    {{ $testMessage }}
                </p>
            </div>
        @endif
    </div>

    {{-- Form Actions --}}
    <div class="flex justify-between items-center pt-4 border-t border-gray-200">
        <a href="{{ route('devices.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            {{ __('Cancel') }}
        </a>
        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            {{ $device ? __('Update Device') : __('Create Device') }}
        </button>
    </div>
</div>

