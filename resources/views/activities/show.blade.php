<x-admin-layout>
    <div class="min-h-screen bg-gray-50">
        <div class="w-full max-w-5xl mx-auto px-6 lg:px-10 py-8">
            <div class="mb-6 flex items-center justify-between">
                <a href="{{ route('activities.index', ['month' => request('month', now()->format('Y-m'))]) }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to My Activity/Travel
                </a>
                <div class="text-sm text-gray-500">Activity/Travel ID: {{ $activity->id }}</div>
            </div>

            <div class="bg-white shadow rounded-lg p-6 space-y-6">
                <h1 class="text-2xl font-semibold text-gray-900">Activity/Travel Details</h1>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <p class="text-xs text-gray-500">Employee</p>
                        <p class="text-sm text-gray-900">{{ $activity->employee->first_name }} {{ $activity->employee->last_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Date</p>
                        <p class="text-sm text-gray-900">{{ $activity->date?->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">End Date</p>
                        <p class="text-sm text-gray-900">{{ $activity->end_date?->format('M d, Y') ?? '—' }}</p>
                    </div>
                </div>

                <div>
                    <p class="text-xs text-gray-500">Activity/Travel Title</p>
                    <p class="text-sm text-gray-900">{{ $activity->activity_type }}</p>
                </div>

                <div>
                    <p class="text-xs text-gray-500">Description</p>
                    <p class="text-sm text-gray-900 whitespace-pre-line">{{ $activity->description ?? '—' }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <p class="text-xs text-gray-500">Memo</p>
                        @if(!empty($activity->memorandum_link))
                            <a href="{{ $activity->memorandum_link }}" target="_blank" class="text-blue-600 underline">Open</a>
                        @else
                            <p class="text-sm text-gray-900">—</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Certificate of Appearance/Travel Form</p>
                        @if(!empty($activity->certificate_attachment))
                            <a href="{{ asset('storage/' . $activity->certificate_attachment) }}" target="_blank" class="text-green-600 underline">Open</a>
                        @else
                            <p class="text-sm text-gray-900">—</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">ATT/LOC</p>
                        @if(!empty($activity->att_attachment))
                            <a href="{{ asset('storage/' . $activity->att_attachment) }}" target="_blank" class="text-yellow-600 underline">Open</a>
                        @else
                            <p class="text-sm text-gray-900">—</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reuse modal markup from index for consistency -->
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
                <div id="modalContent" class="w-full h-full"></div>
            </div>
            <div class="flex justify-end gap-3 p-6 border-t border-gray-200 shrink-0">
                <div class="mr-auto flex gap-2">
                    <button onclick="zoomOut()" class="px-3 py-2 bg-gray-100 text-gray-900 rounded-lg hover:bg-gray-200" title="Zoom Out">−</button>
                    <button onclick="zoomIn()" class="px-3 py-2 bg-gray-100 text-gray-900 rounded-lg hover:bg-gray-200" title="Zoom In">+</button>
                    <button onclick="resetZoom()" class="px-3 py-2 bg-gray-100 text-gray-900 rounded-lg hover:bg-gray-200" title="Reset Zoom">Reset</button>
                    <button onclick="printAttachment()" class="px-3 py-2 bg-gray-100 text-gray-900 rounded-lg hover:bg-gray-200" title="Print">Print</button>
                </div>
                <button id="toggleSizeBtn" onclick="toggleAttachmentFullscreen()" class="px-4 py-2 bg-gray-100 text-gray-900 rounded-lg hover:bg-gray-200">Maximize</button>
                <button onclick="closeAttachmentModal()" class="px-4 py-2 bg-gray-200 text-gray-900 rounded-lg hover:bg-gray-300">Close</button>
                <a id="openExternalLink" href="#" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Open in New Tab</a>
            </div>
        </div>
    </div>

    <script>
        let currentAttachmentUrl = null;
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
            try {
                const win = window.open(currentAttachmentUrl, '_blank');
                if (!win) return;
                win.addEventListener('load', () => { try { win.print(); } catch (e) {} });
            } catch (e) {
                window.open(currentAttachmentUrl, '_blank');
            }
        }

        function openAttachmentModal(activityId, type, url) {
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

            if (type === 'memo') {
                title.textContent = 'Memorandum PDF';
            } else if (type === 'ca') {
                    title.textContent = 'Certificate of Appearance/Travel Form';
            } else if (type === 'att') {
                title.textContent = 'Authority to Travel/Locator (ATT/LOC)';
            }

            currentAttachmentUrl = url;
            externalLink.href = url;

            if (type === 'memo') {
                let embedUrl = url;
                if (url.includes('drive.google.com')) {
                    let fileId = '';
                    if (url.includes('/d/')) { fileId = url.split('/d/')[1].split('/')[0]; }
                    else if (url.includes('id=')) { fileId = url.split('id=')[1].split('&')[0]; }
                    if (fileId) { embedUrl = `https://drive.google.com/file/d/${fileId}/preview`; }
                }
                content.innerHTML = `<div id="modalInner" class="w-full h-full"><iframe src="${embedUrl}" class="w-full h-full" frameborder="0" allow="autoplay"></iframe></div>`;
            } else {
                const fileExt = url.split('.').pop().toLowerCase();
                if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExt)) {
                    content.innerHTML = `<div id="modalInner" class="w-full h-full"><img src="${url}" alt="Attachment" class="w-full h-full object-contain"></div>`;
                } else if (fileExt === 'pdf') {
                    content.innerHTML = `<div id="modalInner" class="w-full h-full"><iframe src="${url}" class="w-full h-full" frameborder="0"></iframe></div>`;
                } else {
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

            modal.classList.remove('hidden');
        }

        function closeAttachmentModal() {
            const modal = document.getElementById('attachmentModal');
            modal.classList.add('hidden');
        }

        document.getElementById('attachmentModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeAttachmentModal();
            }
        });
    </script>
</x-admin-layout>
