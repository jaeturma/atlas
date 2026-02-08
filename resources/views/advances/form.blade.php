<div class="p-6">
    <!-- Section: Employee & Basic Info -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Employee Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
                <label for="employee_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Employee <span class="text-red-500">*</span></label>
                <select name="employee_id" id="employee_id" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="">-- Select Employee --</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" {{ old('employee_id', $advance->employee_id ?? '') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->last_name }}, {{ $employee->first_name }} {{ $employee->middle_name }}
                        </option>
                    @endforeach
                </select>
                @error('employee_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date <span class="text-red-500">*</span></label>
                <input type="date" name="date" id="date" value="{{ old('date', isset($advance->date) ? $advance->date->format('Y-m-d') : date('Y-m-d')) }}" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                @error('date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type <span class="text-red-500">*</span></label>
                @php($type = old('type', $advance->type ?? 'cash'))
                <select name="type" id="type" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="cash" {{ $type === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="product" {{ $type === 'product' ? 'selected' : '' }}>Product</option>
                    <option value="service" {{ $type === 'service' ? 'selected' : '' }}>Service</option>
                </select>
                @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <!-- Section: Advance Details -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Advance Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
                <label for="total_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Total Amount <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">₱</span>
                    </div>
                    <input type="number" step="0.01" name="total_amount" id="total_amount" value="{{ old('total_amount', $advance->total_amount ?? '') }}" class="block w-full pl-7 pr-3 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="0.00" required>
                </div>
                @error('total_amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="reference" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reference Number</label>
                <input type="text" name="reference" id="reference" value="{{ old('reference', $advance->reference ?? '') }}" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="e.g., ADV-2026-001">
                @error('reference') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                @php($status = old('status', $advance->status ?? 'open'))
                <select name="status" id="status" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="open" {{ $status === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="settled" {{ $status === 'settled' ? 'selected' : '' }}>Settled</option>
                    <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <!-- Section: Additional Information -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Additional Information</h3>
        <div class="grid grid-cols-1 gap-6">
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description / Notes</label>
                <textarea name="description" id="description" rows="4" class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Enter any additional details or notes about this advance...">{{ old('description', $advance->description ?? '') }}</textarea>
                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center">
                @php($auto = old('auto_deduct', $advance->auto_deduct ?? false))
                <input type="hidden" name="auto_deduct" value="0">
                <input type="checkbox" name="auto_deduct" id="auto_deduct" value="1" {{ $auto ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700">
                <label for="auto_deduct" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Automatically deduct from payroll</label>
                <p class="ml-2 text-xs text-gray-500 dark:text-gray-400">(Full balance will be deducted when generating payroll deductions)</p>
            </div>
        </div>
    </div>
</div>
