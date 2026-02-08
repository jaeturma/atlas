<form wire:submit="save" class="space-y-6">
    {{-- Department Information Section --}}
    <div>
        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Department Information') }}</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Name') }} <span class="text-red-500">*</span></label>
                <input wire:model="name" type="text" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700">{{ __('Code') }}</label>
                <input wire:model="code" type="text" id="code" placeholder="e.g. HR, IT, FIN" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('code') border-red-500 @enderror">
                @error('code') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="grid grid-cols-1 gap-4 mt-4">
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
                <textarea wire:model="description" id="description" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-500 @enderror"></textarea>
                @error('description') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4 mt-4">
            <div>
                <label for="head_name" class="block text-sm font-medium text-gray-700">{{ __('Department Head Name') }}</label>
                <input wire:model="head_name" type="text" id="head_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('head_name') border-red-500 @enderror">
                @error('head_name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="head_position_title" class="block text-sm font-medium text-gray-700">{{ __('Department Head Position Title') }}</label>
                <input wire:model="head_position_title" type="text" id="head_position_title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('head_position_title') border-red-500 @enderror">
                @error('head_position_title') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="mt-4">
            <label for="is_active" class="flex items-center">
                <input wire:model.live="is_active" type="checkbox" id="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <span class="ml-2 text-sm font-medium text-gray-900">{{ __('Active') }}</span>
            </label>
        </div>
    </div>

    {{-- Form Actions --}}
    <div class="flex justify-between items-center pt-4 border-t border-gray-200">
        <a href="{{ route('departments.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            {{ __('Cancel') }}
        </a>
        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            {{ $this->department ? __('Update Department') : __('Create Department') }}
        </button>
    </div>
</form>

