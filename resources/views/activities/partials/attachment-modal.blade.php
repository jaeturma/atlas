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
            try {
                const win = window.open(currentAttachmentUrl, '_blank');
                if (!win) return;
                win.addEventListener('load', () => {
                    try { win.print(); } catch (e) { /* ignore */ }
                });
            } catch (e) {
                window.open(currentAttachmentUrl, '_blank');
            }
        }

        function openAttachmentModal(id, type, url) {
            if (!url) return;
            currentAttachmentUrl = url;
            currentAttachmentType = type;

            const modal = document.getElementById('attachmentModal');
            const content = document.getElementById('modalContent');
            const link = document.getElementById('openExternalLink');
            if (!modal || !content) return;

            if (link) link.href = url;

            let html = '';
            if (url.endsWith('.pdf') || type === 'memo') {
                html = `<iframe id="modalInner" src="${url}" class="w-full h-full" frameborder="0"></iframe>`;
            } else {
                html = `<img id="modalInner" src="${url}" class="max-w-full" />`;
            }

            content.innerHTML = html;
            modal.classList.remove('hidden');
            setAttachmentModalSize(false);
            setZoom(1);
        }

        function closeAttachmentModal() {
            const modal = document.getElementById('attachmentModal');
            const content = document.getElementById('modalContent');
            if (content) content.innerHTML = '';
            if (modal) modal.classList.add('hidden');
            currentAttachmentUrl = null;
            currentAttachmentType = null;
        }
    </script>