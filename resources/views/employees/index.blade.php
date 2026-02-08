<x-admin-layout>
    <div class="min-h-screen bg-gray-50">
        <div class="mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Employees</h1>
                    <p class="mt-1 text-sm text-gray-600">Manage employee records, positions, and groups</p>
                </div>
                @unless(auth()->user()?->hasRole('DTR Incharge'))
                <div class="flex gap-2">
                    <button type="button" onclick="document.getElementById('importModal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Import CSV
                    </button>
                    <a href="{{ route('employees.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 whitespace-nowrap">
                        + Add Employee
                    </a>
                </div>
                @endunless
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <livewire:employee-list />
                </div>
            </div>

            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('import_errors'))
                <div class="mb-4 rounded-md bg-yellow-50 p-4 border border-yellow-200">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-.75-5.5a.75.75 0 011.5 0v1.5a.75.75 0 01-1.5 0v-1.5zm0-6a.75.75 0 011.5 0v4.5a.75.75 0 01-1.5 0v-4.5z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-yellow-800">Some rows failed to import:</p>
                            <ul class="mt-2 text-sm text-yellow-800 list-disc list-inside space-y-1">
                                @foreach (session('import_errors') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            @unless(auth()->user()?->hasRole('DTR Incharge'))
            <!-- Import Modal -->
            <div id="importModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-gray-900">Import Employees from CSV</h2>
                        <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded">
                        <p class="text-sm text-blue-800">
                            <strong>CSV Format:</strong> badge_number, first_name, middle_name, last_name, suffix, birthdate, gender, civil_status, email, phone, address, emergency_contact, emergency_phone, tin, sss, philhealth, pagibig, department_id, position_id, salary
                        </p>
                        <a href="{{ route('employees.template') }}" class="text-sm text-blue-600 hover:text-blue-800 underline mt-1 inline-block">
                            Download sample template
                        </a>
                    </div>

                    <form id="importForm" action="{{ route('employees.import') }}" method="POST" enctype="multipart/form-data" onsubmit="showProgress()">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select CSV File</label>
                            <input type="file" name="csv_file" accept=".csv,.txt" required 
                                   class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                            <p class="mt-1 text-xs text-gray-500">Max file size: 2MB</p>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div id="importProgress" class="hidden mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Importing employees...</span>
                                <span id="progressPercent" class="text-sm font-bold text-blue-600">0%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div id="progressBar" class="bg-blue-600 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                        </div>
                        
                        <div id="importButtons" class="flex justify-end gap-3">
                            <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" 
                                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endunless
        </div>
    </div>

    <script>
        let progressInterval;
        
        function showProgress() {
            document.getElementById('importProgress').classList.remove('hidden');
            document.getElementById('importButtons').classList.add('hidden');
            
            // Start polling for progress
            progressInterval = setInterval(updateProgress, 500);
        }
        
        function updateProgress() {
            fetch('{{ route('import.progress') }}')
                .then(response => response.json())
                .then(data => {
                    const progress = data.progress || 0;
                    document.getElementById('progressBar').style.width = progress + '%';
                    document.getElementById('progressPercent').textContent = progress + '%';
                    
                    if (progress >= 100) {
                        clearInterval(progressInterval);
                    }
                })
                .catch(error => console.error('Error fetching progress:', error));
        }
    </script>
</x-admin-layout>
