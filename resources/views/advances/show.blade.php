<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <span class="text-2xl font-semibold text-gray-900 dark:text-white">Advance Details</span>
            <div class="flex gap-2">
                <a href="{{ route('advances.edit', $advance) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 text-sm">Edit</a>
                <a href="{{ route('advances.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm">Back to list</a>
            </div>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-4 p-4 rounded-lg bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-800">
            <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <p class="text-sm text-gray-500">Employee</p>
                    <p class="text-base font-medium">{{ $advance->employee->getFullName() }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Type</p>
                    <p class="text-base font-medium">{{ ucfirst($advance->type) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Date</p>
                    <p class="text-base font-medium">{{ $advance->date->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Reference</p>
                    <p class="text-base font-medium">{{ $advance->reference }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Status</p>
                    <p class="text-base font-medium">{{ ucfirst($advance->status) }}</p>
                </div>
                @if($advance->description)
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500">Description</p>
                    <p class="text-base font-medium">{{ $advance->description }}</p>
                </div>
                @endif
                <div>
                    <p class="text-sm text-gray-500">Total Amount</p>
                    <p class="text-base font-medium">{{ number_format($advance->total_amount, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Remaining Balance</p>
                    <p class="text-base font-medium">{{ number_format($advance->remaining_balance, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Auto Deduct</p>
                    <p class="text-base font-medium">{{ $advance->auto_deduct ? 'Yes' : 'No' }}</p>
                </div>
            </div>

            <h3 class="text-lg font-semibold mb-3">Ledger</h3>
            <div class="mb-4">
                <form method="POST" action="{{ route('advances.ledger.add', $advance) }}" class="grid grid-cols-1 md:grid-cols-5 gap-2">
                    @csrf
                    <input type="date" name="entry_date" class="border-gray-300 rounded" required>
                    <select name="entry_type" class="border-gray-300 rounded">
                        <option value="debit">Debit (Increase)</option>
                        <option value="credit">Credit (Payment/Deduction)</option>
                    </select>
                    <input type="number" step="0.01" name="amount" placeholder="Amount" class="border-gray-300 rounded" required>
                    <input type="text" name="source" placeholder="Source (e.g., payroll)" class="border-gray-300 rounded">
                    <button type="submit" class="px-3 py-2 bg-primary-600 text-white rounded hover:bg-primary-700">Add Entry</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">Date</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">Type</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">Amount</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">Source</th>
                            <th class="px-4 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($advance->ledgers as $entry)
                            <tr>
                                <td class="px-4 py-2">{{ $entry->entry_date->format('Y-m-d') }}</td>
                                <td class="px-4 py-2 capitalize">{{ $entry->entry_type }}</td>
                                <td class="px-4 py-2">{{ number_format($entry->amount, 2) }}</td>
                                <td class="px-4 py-2">{{ $entry->source }}</td>
                                <td class="px-4 py-2">{{ $entry->notes }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">No ledger entries yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
