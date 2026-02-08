<x-admin-layout>
    {{-- No sidebar/header title --}}
    <div class="min-h-screen bg-gray-50">
    <div class="w-full max-w-screen-2xl mx-auto px-6 lg:px-10 py-8">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                    <h1 class="text-3xl font-bold text-gray-900">My Activity/Travel</h1>
                    <p class="mt-1 text-sm text-gray-600">Manage employee activity/travel</p>
            </div>
            <a href="{{ route('activities.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                + New Activity/Travel
            </a>
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

        <!-- Filters -->
        <div class="mb-4">
            <form action="{{ route('activities.index') }}" method="GET" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label for="search" class="block text-xs font-medium text-gray-700">Search</label>
                    <input type="text" id="search" name="search" value="{{ request('search', $search ?? '') }}" placeholder="Employee, Activity/Travel Title, Description..." class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
                <div>
                    <label for="month" class="block text-xs font-medium text-gray-700">Month</label>
                    <input type="month" id="month" name="month" value="{{ request('month', $selectedMonth ?? now()->format('Y-m')) }}" class="mt-1 block border border-gray-300 rounded-md px-3 py-2">
                </div>
                <div>
                    <label for="per_page" class="block text-xs font-medium text-gray-700">Rows</label>
                    <select id="per_page" name="per_page" class="mt-1 block border border-gray-300 rounded-md px-3 py-2">
                        <option value="10" {{ request('per_page', $perPage ?? 15) == 10 ? 'selected' : '' }}>10</option>
                        <option value="15" {{ request('per_page', $perPage ?? 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page', $perPage ?? 15) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', $perPage ?? 15) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', $perPage ?? 15) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <div class="flex gap-3 items-end">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Filter</button>
                    <a href="{{ route('activities.index', ['month' => now()->format('Y-m')]) }}" class="px-4 py-2 bg-gray-200 text-gray-900 rounded-md hover:bg-gray-300">Reset</a>
                </div>
            </form>
        </div>

        <!-- Activities Table -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-400">
            @if($activities->count() > 0)
                <div class="p-4 overflow-x-auto">
                <table id="activitiesTable" class="min-w-full divide-y divide-gray-200 border border-gray-300 dark:border-gray-600">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">No.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">Date / Activity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">Memo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">CA/Travel</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">ATT/LOC</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($activities as $activity)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ ($activities->currentPage() - 1) * $activities->perPage() + $loop->iteration }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $activity->employee->first_name }} {{ $activity->employee->last_name }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                        @if($activity->end_date && $activity->end_date->format('Y-m-d') !== $activity->date->format('Y-m-d'))
                                            {{ $activity->date->format('M d') }}-{{ $activity->end_date->format('d, Y') }}
                                        @else
                                            {{ $activity->date->format('M d, Y') }}
                                        @endif
                                    </div>
                                    <div class="mt-1 text-sm font-semibold text-blue-800 dark:text-blue-200 whitespace-normal break-words max-w-[260px]">
                                        {!! nl2br(e(wordwrap($activity->activity_type, 40, "\n", true))) !!}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-700">
                                    @if(!empty($activity->memorandum_link))
                                        <button onclick="openAttachmentModal({{ $activity->id }}, 'memo', '{{ $activity->memorandum_link }}')" class="hover:text-blue-900" title="View Memorandum">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-700">
                                    @if(!empty($activity->certificate_attachment))
                                        <button onclick="openAttachmentModal({{ $activity->id }}, 'ca', '{{ asset('storage/' . $activity->certificate_attachment) }}')" class="hover:text-green-900" title="View CA/Travel">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-700">
                                    @if(!empty($activity->att_attachment))
                                        <button onclick="openAttachmentModal({{ $activity->id }}, 'att', '{{ asset('storage/' . $activity->att_attachment) }}')" class="hover:text-yellow-900" title="View ATT/LOC">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('activities.show', $activity) }}" class="px-3 py-1 bg-gray-800 text-white rounded hover:bg-gray-900 text-xs">View</a>
                                        <a href="{{ route('activities.edit', $activity) }}" class="px-3 py-1 bg-blue-700 text-white rounded hover:bg-blue-800 text-xs">Edit</a>
                                        <form action="{{ route('activities.destroy', $activity) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this activity?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1 bg-red-700 text-white rounded hover:bg-red-800 text-xs">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400">No activity/travel records found.</p>
                    <a href="{{ route('activities.create') }}" class="mt-4 inline-block text-blue-600 hover:text-blue-900">Create your first activity/travel</a>
                </div>
            @endif
        </div>

        @if($activities->count() > 0)
            <div class="mt-6">
                {{ $activities->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
    </div>

    <!-- Attachment Modal -->
    <div id="attachmentModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div id="attachmentModalPanel" class="bg-white rounded-xl shadow-xl w-[70vw] h-[70vh] flex flex-col overflow-hidden">
            <div class="flex items-center justify-between p-6 border-b border-gray-200 shrink-0">
                <h3 id="modalTitle" class="text-xl font-semibold text-gray-900">Attachment</h3>
                <button onclick="closeAttachmentModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6 flex-1 overflow-auto">
                <div id="modalContent" class="w-full h-full">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="flex justify-end gap-3 p-6 border-t border-gray-200 shrink-0">
                <div class="mr-auto flex gap-2">
                    <button onclick="zoomOut()" class="px-3 py-2 bg-gray-100 text-gray-900 rounded-lg hover:bg-gray-200" title="Zoom Out">−</button>
                    <button onclick="zoomIn()" class="px-3 py-2 bg-gray-100 text-gray-900 rounded-lg hover:bg-gray-200" title="Zoom In">+</button>
                    <button onclick="resetZoom()" class="px-3 py-2 bg-gray-100 text-gray-900 rounded-lg hover:bg-gray-200" title="Reset Zoom">Reset</button>
                    <button onclick="printAttachment()" class="px-3 py-2 bg-gray-100 text-gray-900 rounded-lg hover:bg-gray-200" title="Print">Print</button>
                </div>
                <button id="toggleSizeBtn" onclick="toggleAttachmentFullscreen()" class="px-4 py-2 bg-gray-100 text-gray-900 rounded-lg hover:bg-gray-200">
                    Maximize
                </button>
                <button onclick="closeAttachmentModal()" class="px-4 py-2 bg-gray-200 text-gray-900 rounded-lg hover:bg-gray-300">
                    Close
                </button>
                <a id="openExternalLink" href="#" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Open in New Tab
                </a>
            </div>
        </div>
    </div>

    <script>
        let currentAttachmentUrl = null;
        let currentAttachmentType = null;
        let currentZoom = 1;

        function setAttachmentModalSize(isFull) {
            const overlay = document.getElementById('attachmentModal');
            const panel = document.getElementById('attachmentModalPanel');
            const toggleBtn = document.getElementById('toggleSizeBtn');
            if (!overlay || !panel) return;

            if (isFull) {
                overlay.classList.remove('p-4');
                overlay.classList.add('p-0');
                panel.className = 'bg-white rounded-none shadow-xl w-screen h-screen flex flex-col overflow-hidden';
                if (toggleBtn) toggleBtn.textContent = 'Restore';
            } else {
                overlay.classList.remove('p-0');
                overlay.classList.add('p-4');
                panel.className = 'bg-white rounded-xl shadow-xl w-[70vw] h-[70vh] flex flex-col overflow-hidden';
                if (toggleBtn) toggleBtn.textContent = 'Maximize';
            }
        }

        function toggleAttachmentFullscreen() {
            const overlay = document.getElementById('attachmentModal');
            const isFull = overlay.classList.contains('p-0');
            setAttachmentModalSize(!isFull);
        }

        function setZoom(z) {
            currentZoom = Math.max(0.25, Math.min(4, z));
            const inner = document.getElementById('modalInner');
            if (inner) {
                inner.style.transform = 'scale(' + currentZoom + ')';
                inner.style.transformOrigin = 'top left';
            }
        }

        function zoomIn() { setZoom(currentZoom + 0.25); }
        function zoomOut() { setZoom(currentZoom - 0.25); }
        function resetZoom() { setZoom(1); }

        function printAttachment() {
            if (!currentAttachmentUrl) return;
            // For same-origin files, open and auto-print; for cross-origin (e.g., Google Drive), open new tab
            try {
                const win = window.open(currentAttachmentUrl, '_blank');
                if (!win) return; // Popup blocked
                win.addEventListener('load', () => {
                    try { win.print(); } catch (e) { /* silently ignore cross-origin */ }
                });
            } catch (e) {
                window.open(currentAttachmentUrl, '_blank');
            }
        }

        function openAttachmentModal(activityId, type, url) {
            // Default to reduced size each time the modal opens
            setAttachmentModalSize(false);
            resetZoom();
            const modal = document.getElementById('attachmentModal');
            const title = document.getElementById('modalTitle');
            const content = document.getElementById('modalContent');
            const externalLink = document.getElementById('openExternalLink');

            if (!url || url.trim() === '') {
                alert('No attachment available');
                return;
            }

            // Set title based on type
            if (type === 'memo') {
                title.textContent = 'Memorandum PDF';
            } else if (type === 'ca') {
                title.textContent = 'Certificate of Appearance/Travel Form';
            } else if (type === 'att') {
                title.textContent = 'Authority to Travel/Locator (ATT/LOC)';
            }

            // Track current attachment
            currentAttachmentUrl = url;
            currentAttachmentType = type;

            // Set external link
            externalLink.href = url;

            // Display content based on type
            if (type === 'memo') {
                // For Google Drive PDF links, convert to embed URL
                let embedUrl = url;
                if (url.includes('drive.google.com')) {
                    // Extract file ID from various Google Drive URL formats
                    let fileId = '';
                    if (url.includes('/d/')) {
                        fileId = url.split('/d/')[1].split('/')[0];
                    } else if (url.includes('id=')) {
                        fileId = url.split('id=')[1].split('&')[0];
                    }
                    if (fileId) {
                        embedUrl = `https://drive.google.com/file/d/${fileId}/preview`;
                    }
                }
                content.innerHTML = `<div id="modalInner" class="w-full h-full"><iframe src="${embedUrl}" class="w-full h-full" frameborder="0" allow="autoplay"></iframe></div>`;
            } else {
                // For uploaded files (CA and ATT), show preview or icon
                const fileExt = url.split('.').pop().toLowerCase();
                
                if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExt)) {
                    // Image preview
                    content.innerHTML = `<div id="modalInner" class="w-full h-full"><img src="${url}" alt="Attachment" class="w-full h-full object-contain"></div>`;
                } else if (fileExt === 'pdf') {
                    // PDF embed
                    content.innerHTML = `<div id="modalInner" class="w-full h-full"><iframe src="${url}" class="w-full h-full" frameborder="0"></iframe></div>`;
                } else {
                    // Generic file icon
                    content.innerHTML = `
                        <div id="modalInner" class="flex flex-col items-center justify-center h-full">
                            <svg class="w-16 h-16 text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <p class="text-center text-gray-700">File: ${url.split('/').pop()}</p>
                        </div>
                    `;
                }
            }
            resetZoom();

            // Show modal
            modal.classList.remove('hidden');
        }

        function closeAttachmentModal() {
            const modal = document.getElementById('attachmentModal');
            modal.classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('attachmentModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeAttachmentModal();
            }
        });
    </script>
</x-admin-layout>
