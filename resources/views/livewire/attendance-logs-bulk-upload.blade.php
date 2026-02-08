<div class="space-y-4" x-data="bulkUpload()">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Bulk Upload Logs</h3>
    
    <!-- Info Alert -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 flex gap-2">
        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd"/>
        </svg>
        <p class="text-sm text-blue-700 dark:text-blue-300"><strong>Tip:</strong> Device is automatically mapped to your USB device (Device 1). Just upload your file and select the date range.</p>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
        <div class="space-y-4">
            <!-- File Upload (Primary) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Upload Log File from USB</label>
                <div class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 hover:border-blue-400 dark:hover:border-blue-500 transition-colors cursor-pointer" 
                     @dragover.prevent="dragActive = true"
                     @dragleave.prevent="dragActive = false"
                     @drop.prevent="handleDrop"
                     :class="dragActive && 'border-blue-400 dark:border-blue-500 bg-blue-50 dark:bg-blue-900/10'">
                    <input type="file" id="fileInput" @change="handleFileSelect" accept=".csv,.log,.dat,.txt" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20a4 4 0 004 4h24a4 4 0 004-4V20m-8-12v12m0 0l-3-3m3 3l3-3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                            <span class="font-medium">Click to upload</span> or drag and drop
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">CSV, LOG, DAT, or TXT files up to 100MB</p>
                        <p x-show="selectedFileName" class="mt-2 text-sm text-green-600 dark:text-green-400">✓ <span x-text="selectedFileName"></span></p>
                    </div>
                </div>
                <p x-show="fileError" x-text="fileError" class="text-red-600 text-sm mt-1"></p>
            </div>

            <!-- Device Info (Auto-mapped to Device 1) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Device Mapping</label>
                <div class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white">
                    <p class="text-sm">📱 Automatically mapped to <strong>{{ $devices->where('id', 1)->first()?->name ?? 'Device 1' }}</strong> (USB)</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">All logs will be imported for this device</p>
                </div>
            </div>

            <!-- Date Range Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Start Date</label>
                    <input type="date" wire:model="startDate" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Default: 1st of current month</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">End Date</label>
                    <input type="date" wire:model="endDate" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Default: Today</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3">
                <button 
                    type="button"
                    wire:click="uploadLogs"
                    wire:loading.attr="disabled"
                    :disabled="$totalRecords === 0 || $uploading"
                    class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span wire:loading.remove>Upload All</span>
                    <span wire:loading>Uploading...</span>
                </button>

                <button 
                    type="button"
                    wire:click="resetForm"
                    :disabled="!$uploadedFileName && $totalRecords === 0"
                    class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9M20 20v-5h-.581m-15.357-2a8.003 8.003 0 0115.357 2"/>
                    </svg>
                    Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Extracted Logs Summary -->
    @if ($totalRecords > 0 && !$uploading)
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h4 class="font-semibold text-blue-900 dark:text-blue-100">Ready to Upload</h4>
                    <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                        Found <span class="font-bold">{{ $totalRecords }}</span> records 
                        from <strong>{{ Carbon\Carbon::parse($startDate)->format('M d, Y') }}</strong> 
                        to <strong>{{ Carbon\Carbon::parse($endDate)->format('M d, Y') }}</strong>
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Progress Bar -->
    @if ($uploading)
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 space-y-3">
            <div class="flex justify-between items-center">
                <h4 class="font-semibold text-gray-900 dark:text-gray-100">Upload Progress</h4>
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $progress }}%</span>
            </div>

            <div class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div 
                    class="h-full bg-gradient-to-r from-green-500 to-green-600 rounded-full transition-all duration-300"
                    style="width: {{ $progress }}%">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 text-center text-sm">
                <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded-lg border border-green-200 dark:border-green-800">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $savedRecords }}</div>
                    <div class="text-xs text-green-700 dark:text-green-300">Saved</div>
                </div>
                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-lg border border-yellow-200 dark:border-yellow-800">
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $skippedRecords }}</div>
                    <div class="text-xs text-yellow-700 dark:text-yellow-300">Skipped</div>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg border border-blue-200 dark:border-blue-800">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $totalRecords }}</div>
                    <div class="text-xs text-blue-700 dark:text-blue-300">Total</div>
                </div>
            </div>
        </div>
    @endif

    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 flex items-start gap-3">
            <svg class="h-5 w-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <div>
                <h4 class="font-semibold text-green-900 dark:text-green-100">Success</h4>
                <p class="text-sm text-green-700 dark:text-green-300 mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 flex items-start gap-3">
            <svg class="h-5 w-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <div>
                <h4 class="font-semibold text-red-900 dark:text-red-100">Error</h4>
                <p class="text-sm text-red-700 dark:text-red-300 mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
    @endif
</div>

<script>
function bulkUpload() {
    return {
        dragActive: false,
        selectedFileName: '',
        fileError: '',
        uploading: false,
        totalRecords: @js($totalRecords),
        savedRecords: @js($savedRecords),
        skippedRecords: @js($skippedRecords),
        uploadedFilePath: null,

        handleFileSelect(e) {
            this.fileError = '';
            const files = e.target.files;
            if (files.length === 0) return;

            const file = files[0];
            const extension = file.name.split('.').pop().toLowerCase();

            if (!['csv', 'log', 'dat', 'txt'].includes(extension)) {
                this.fileError = 'Invalid file format. Please upload CSV, LOG, DAT, or TXT file.';
                e.target.value = '';
                this.selectedFileName = '';
                return;
            }

            if (file.size > 100 * 1024 * 1024) {
                this.fileError = 'File size exceeds 100MB limit.';
                e.target.value = '';
                this.selectedFileName = '';
                return;
            }

            this.uploadFileToServer(file);
        },

        handleDrop(e) {
            this.dragActive = false;
            const files = e.dataTransfer.files;
            if (files.length === 0) return;

            const file = files[0];
            const extension = file.name.split('.').pop().toLowerCase();

            if (!['csv', 'log', 'dat', 'txt'].includes(extension)) {
                this.fileError = 'Invalid file format. Please upload CSV, LOG, DAT, or TXT file.';
                this.selectedFileName = '';
                return;
            }

            this.uploadFileToServer(file);
        },

        uploadFileToServer(file) {
            this.uploading = true;
            this.selectedFileName = file.name;
            this.fileError = '';

            const formData = new FormData();
            formData.append('file', file);

            console.log('Uploading file:', file.name);

            fetch('{{ route("attendance-logs.upload") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: formData,
            })
            .then(response => {
                console.log('Upload response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Upload response data:', data);
                this.uploading = false;
                
                if (data.success && data.filePath) {
                    this.uploadedFilePath = data.filePath;
                    this.fileError = '';
                    console.log('File uploaded successfully');
                    console.log('File path:', data.filePath);
                    console.log('Dispatching file-uploaded event with filePath');
                    // Dispatch to Livewire component
                    window.Livewire.dispatch('file-uploaded', { filePath: data.filePath });
                    console.log('Event dispatched');
                } else {
                    const errorMsg = data.error || 'File upload failed';
                    console.error('Upload error:', errorMsg);
                    this.fileError = errorMsg;
                    this.selectedFileName = '';
                    document.getElementById('fileInput').value = '';
                }
            })
            .catch(error => {
                this.uploading = false;
                console.error('Network error:', error);
                this.fileError = 'Network error: ' + error.message;
                this.selectedFileName = '';
                document.getElementById('fileInput').value = '';
            });
        },
    };
}
</script>
</div>
