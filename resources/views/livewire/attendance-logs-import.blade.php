<div x-data="attendanceImport()" 
     @import-saving-start.window="onSavingStart($event)" 
     @import-saving-progress.window="onSavingProgress($event)" 
     @import-saving-complete.window="onSavingComplete($event)"
     class="space-y-4">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">USB Import</h3>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
        <div class="flex gap-4 items-end">
            <div class="flex-1">
                <div 
                    @drop.prevent="onFileDrop($event)" 
                    @dragover.prevent="isDragging=true" 
                    @dragleave.prevent="isDragging=false"
                    @drop="isDragging=false"
                    :class="isDragging ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-400 dark:border-blue-600' : 'bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-600'"
                    class="relative border-2 border-dashed rounded-lg p-4 transition-colors cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-400 dark:hover:border-blue-600">
                    
                    <input 
                        type="file" 
                        accept=".log,.dat,.csv" 
                        @change="onFile($event)" 
                        x-ref="fileInput"
                        class="hidden" />
                    
                    <div @click="$refs.fileInput.click()" class="cursor-pointer flex items-center justify-between">
                        <div class="flex items-center gap-2 flex-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            <span x-show="!file" class="text-sm text-gray-900 dark:text-gray-100">Upload Log File from USB</span>
                            <span x-show="file" class="text-sm text-gray-900 dark:text-gray-100 truncate"><span class="font-medium" x-text="file.name"></span></span>
                        </div>
                        <span x-show="file" class="text-xs text-gray-500 dark:text-gray-400 ml-2 flex-shrink-0" x-text="`${(file.size / 1024 / 1024).toFixed(2)} MB`"></span>
                    </div>
                </div>
            </div>

            <button type="button" @click="extract()" :disabled="!file || extracting" class="inline-flex items-center px-6 py-4 rounded-md text-base font-medium text-white disabled:opacity-50 bg-blue-600 hover:bg-blue-700 flex-shrink-0 whitespace-nowrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9M20 20v-5h-.581m-15.357-2a8.003 8.003 0 0115.357 2"/></svg>
                Extract
            </button>
        </div>

        <div class="mt-4" x-show="extracting && !saving">
            <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                <span>Extraction Progress</span>
                <span><span x-text="parsed"></span>/<span x-text="total"></span> lines • <span x-text="logs.length"></span> logs</span>
            </div>
            <div class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded overflow-hidden">
                <div class="h-2 rounded transition-all duration-300" :style="`width: ${progress}%; background-color: #2563eb`"></div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden" x-show="logs.length>0">
        <div class="p-4 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <h4 class="font-semibold text-gray-900 dark:text-gray-100">Extracted Logs</h4>
                <div class="flex gap-2">
                    <button type="button" 
                        @click="saveLogs(logs, 1)"
                        :disabled="logs.length===0 || saving" 
                        class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium text-white disabled:opacity-50 bg-green-600 hover:bg-green-700">
                        Save All
                    </button>
                    <button type="button" 
                        @click="saveSelected()"
                        :disabled="selected.size===0 || saving" 
                        class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium text-white disabled:opacity-50 bg-amber-600 hover:bg-amber-700">
                        Save Selected (<span x-text="selected.size"></span>)
                    </button>
                </div>
            </div>
            <!-- Save Progress Bar -->
            <div x-show="saving" class="mt-3">
                <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                    <span x-text="`Storing ${parsed}/${total} records…`"></span>
                    <span x-text="`${Math.round(progress)}%`"></span>
                </div>
                <div class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded overflow-hidden">
                    <div class="h-2 rounded transition-all duration-300" :style="`width: ${progress}%; background-color: #10b981`"></div>
                </div>
            </div>
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-gray-700 dark:text-gray-300">Show rows:</span>
                    <select x-model.number="perPage" @change="currentPage=1" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 pr-12 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Search:</span>
                        <input type="text" x-model.debounce.500ms="searchQuery" placeholder="Badge No." title="Search logs" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-600 dark:text-gray-400">Range:</span>
                        <input type="date" x-model="dateFrom" @change="currentPage=1" title="Date from" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        <span class="text-xs text-gray-600 dark:text-gray-400">to</span>
                        <input type="date" x-model="dateTo" @change="currentPage=1" title="Date to" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-400">
                    <span x-text="pageStart()"></span>–<span x-text="pageEnd()"></span> of <span x-text="filteredLogs().length"></span>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto w-full">
            <table class="w-full min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-100 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-2">
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" :checked="isAllFilteredSelected()" @change="toggleAll($event)" class="rounded border-gray-300 dark:border-gray-600 text-blue-600" />
                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">All</span>
                            </label>
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Badge</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Date/Time</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Device</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Raw</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="(row, idx) in paged()" :key="pageIndex(idx)">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-2">
                                <input type="checkbox" :value="pageIndex(idx)" @change="toggle(pageIndex(idx), $event)" class="rounded border-gray-300 dark:border-gray-600 text-blue-600" />
                            </td>
                            <td class="px-4 py-2 text-sm font-mono" x-text="row.badge"></td>
                            <td class="px-4 py-2 text-sm" x-text="row.logged_at"></td>
                            <td class="px-4 py-2 text-sm">
                                <span x-show="row.deviceId" x-text="row.device_name || 'Unknown'"></span>
                                <span x-show="!row.deviceId" class="text-gray-400">No device</span>
                            </td>
                            <td class="px-4 py-2 text-xs text-gray-500 truncate max-w-xs" :title="row.raw_line" x-text="row.raw_line"></td>
                            <td class="px-4 py-2 text-sm">
                                <button type="button" 
                                    @click="storeSingleRow(row)"
                                    :disabled="saving || stored.has(pageIndex(idx))"
                                    :class="stored.has(pageIndex(idx)) ? 'bg-green-600 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white'"
                                    class="px-2 py-1 text-xs rounded disabled:opacity-50">
                                    <span x-text="stored.has(pageIndex(idx)) ? 'Stored' : 'Store'"></span>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="p-4 flex items-center justify-between border-t border-gray-200 dark:border-gray-700">
            <div class="text-sm text-gray-600 dark:text-gray-400">Page <span x-text="currentPage"></span> of <span x-text="totalPages()"></span></div>
            <div class="flex gap-2">
                <button type="button" @click="prevPage()" :disabled="currentPage<=1" class="px-3 py-1.5 rounded-md text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 disabled:opacity-50">Prev</button>
                <button type="button" @click="nextPage()" :disabled="currentPage>=totalPages()" class="px-3 py-1.5 rounded-md text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 disabled:opacity-50">Next</button>
            </div>
        </div>
    </div>

    <!-- Hidden form for wire:click data passing -->
    <form x-ref="saveForm" style="display:none;">
        <input type="hidden" x-ref="saveData" wire:model="pendingRows">
        <input type="hidden" x-ref="saveTrigger" wire:click="processPendingRows">
    </form>

    <script>
        function attendanceImport() {
            return {
                file: null,
                logs: [],
                selected: new Set(),
                stored: new Set(),
                extracting: false,
                saving: false,
                isDragging: false,
                parsed: 0,
                total: 0,
                perPage: 10,
                currentPage: 1,
                devices: @json($devices),
                selectedDeviceId: null,
                pendingRowsJson: '',
                preparedLogs: [],
                searchQuery: '',
                dateFrom: '',
                dateTo: '',
                get progress() { return this.total ? Math.round(this.parsed / this.total * 100) : 0; },

                init() {
                    console.log('[Alpine] init() called');
                    console.log('[Alpine] $wire available?', !!this.$wire);
                    if (this.$wire) {
                        console.log('[Alpine] $wire methods:', Object.getOwnPropertyNames(Object.getPrototypeOf(this.$wire || {})));
                        console.log('[Alpine] Livewire available, component initialized');
                    } else {
                        console.error('[Alpine] WARNING: $wire not available - Livewire integration may not work');
                    }
                    // Initialize selected device from Livewire if available
                    this.selectedDeviceId = (this.$wire && this.$wire.deviceId) ? this.$wire.deviceId : (this.selectedDeviceId || 1);
                    // Events are now handled via @import-saving-start and @import-saving-complete on the root element
                },
                
                onSavingStart(e) {
                    console.log('[Alpine] onSavingStart', e.detail);
                    this.saving = true;
                    this.extracting = false;
                    this.parsed = 0;
                    this.total = e.detail?.total ?? (this.logs?.length || 0);
                    this.currentPage = 1;
                    console.log('[Alpine] Progress bar started: parsed=0, total=' + this.total);
                },
                
                onSavingProgress(e) {
                    console.log('[Alpine] onSavingProgress', e.detail);
                    this.parsed = e.detail?.processed ?? this.parsed;
                    this.total = e.detail?.total ?? this.total;
                    const pct = this.total ? Math.round(this.parsed / this.total * 100) : 0;
                    console.log('[Alpine] Progress updated: parsed=' + this.parsed + '/' + this.total + ' (' + pct + '%)');
                },
                
                onSavingComplete(e) {
                    console.log('[Alpine] onSavingComplete', e.detail);
                    this.parsed = e.detail?.processed ?? this.total;
                    this.total = e.detail?.total ?? this.total;
                    // Keep saving flag to keep progress bar visible
                    console.log('[Alpine] Saving complete: parsed=' + this.parsed + ', total=' + this.total);
                },

                onFile(e) {
                    this.file = e.target.files[0] || null;
                    this.logs = [];
                    this.selected.clear();
                    this.parsed = 0; this.total = 0;
                },

                onFileDrop(e) {
                    this.isDragging = false;
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        this.file = files[0];
                        this.logs = [];
                        this.selected.clear();
                        this.parsed = 0; this.total = 0;
                    }
                },

                extract() {
                    if (!this.file) return;
                    
                    // Always use device ID 1 for USB imports
                    const selectedDeviceId = 1;
                    const selectedDeviceName = 'USB Import';
                    
                    console.log('[Alpine] Extract started with device:', selectedDeviceId, '-', selectedDeviceName);
                    
                    this.logs = []; this.selected.clear();
                    this.extracting = true;
                    const reader = new FileReader();
                    reader.onload = () => {
                        const text = reader.result || '';
                        const lines = (''+text).split(/\r?\n/);
                        this.total = lines.length;
                        const chunk = 500; // process in chunks for UI updates
                        const processChunk = (start) => {
                            for (let i = start; i < Math.min(start + chunk, lines.length); i++) {
                                const line = lines[i].trim();
                                if (!line) { this.parsed++; continue; }
                                const row = this.parseLine(line);
                                if (row) {
                                    // Add the device that was selected at extraction time
                                    row.deviceId = selectedDeviceId;
                                    row.device_name = selectedDeviceName;
                                    this.logs.push(row);
                                }
                                this.parsed++;
                            }
                            if (this.parsed < lines.length) {
                                setTimeout(() => processChunk(start + chunk), 0);
                            } else {
                                this.extracting = false;
                                this.currentPage = 1;
                            }
                        };
                        processChunk(0);
                    };
                    reader.readAsText(this.file);
                },

                parseLine(line) {
                    // Try to detect datetime in the line
                    const dtMatch = line.match(/(\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2})/);
                    let logged_at = dtMatch ? dtMatch[1].replace('T',' ') : null;
                    let badge = null;
                    let device = 'unknown';

                    // CSV style split first
                    const parts = line.split(',').map(s => s.trim());
                    if (parts.length >= 2) {
                        // Header-aware if first row; but since we parse line-by-line, tolerate either order
                        // Try common columns
                        const headers = parts.map(p => p.toLowerCase());
                        const hasHeaderKeywords = headers.some(h => ['badge','employee','user'].some(k => h.includes(k))) && headers.some(h => ['date','time','datetime','timestamp'].some(k => h.includes(k)));
                        if (!hasHeaderKeywords) {
                            // Assume CSV data row layout like: badge, datetime, device/status...
                            if (!badge) badge = parts[0];
                            if (!logged_at && parts[1]) logged_at = parts[1];
                            if (parts.length >= 3 && parts[2]) device = parts[2];
                        } else {
                            // Likely header row; skip
                            return null;
                        }
                    }

                    if (!badge) {
                        // Find first numeric-like token as badge
                        const token = line.split(/[\s,;]+/).find(t => /^\d+[A-Za-z0-9-]*$/.test(t));
                        if (token) badge = token;
                    }

                    if (!logged_at) {
                        // Try split date and time tokens
                        const dateToken = line.match(/\d{4}-\d{2}-\d{2}/)?.[0];
                        const timeToken = line.match(/\b\d{2}:\d{2}:\d{2}\b/)?.[0];
                        if (dateToken && timeToken) logged_at = `${dateToken} ${timeToken}`;
                    }

                    if (!badge || !logged_at) return null;

                    return { badge, logged_at, device_name: device || 'unknown', raw_line: line };
                },

                // Filter logs based on search and date range
                filteredLogs() {
                    return this.logs.filter(row => {
                        // Search filter - search in badge, logged_at, device_name, and raw_line
                        if (this.searchQuery) {
                            const searchLower = this.searchQuery.toLowerCase();
                            const matchesSearch = (row.badge && row.badge.toLowerCase().includes(searchLower)) ||
                                                 (row.logged_at && row.logged_at.toLowerCase().includes(searchLower)) ||
                                                 (row.device_name && row.device_name.toLowerCase().includes(searchLower)) ||
                                                 (row.raw_line && row.raw_line.toLowerCase().includes(searchLower));
                            if (!matchesSearch) return false;
                        }

                        // Date range filter
                        if (this.dateFrom || this.dateTo) {
                            if (row.logged_at) {
                                const logDate = row.logged_at.split(' ')[0]; // Extract YYYY-MM-DD
                                if (this.dateFrom && logDate < this.dateFrom) return false;
                                if (this.dateTo && logDate > this.dateTo) return false;
                            } else {
                                return false;
                            }
                        }

                        return true;
                    });
                },

                toggle(idx, ev) {
                    if (ev.target.checked) this.selected.add(idx); else this.selected.delete(idx);
                },
                isAllFilteredSelected() {
                    const filtered = this.filteredLogs();
                    if (filtered.length === 0) return false;
                    // Check if all filtered logs are selected
                    return filtered.every(row => this.selected.has(this.logs.indexOf(row)));
                },
                toggleAll(ev) {
                    const filtered = this.filteredLogs();
                    
                    if (ev.target.checked) {
                        // Select all filtered rows
                        filtered.forEach(row => this.selected.add(this.logs.indexOf(row)));
                    } else {
                        // Deselect all filtered rows
                        filtered.forEach(row => this.selected.delete(this.logs.indexOf(row)));
                    }
                },
                getSelectedRows() {
                    return Array.from(this.selected).map(i => this.logs[i]).filter(Boolean);
                },
                saveLogs(rows) {
                    console.log('[Alpine] saveLogs called with rows:', rows.length);
                    if (!rows || rows.length === 0) {
                        alert('No logs to save');
                        return;
                    }
                    // Bridge rows to Livewire via hidden JSON field; wire:click triggers server method
                    this.pendingRowsJson = JSON.stringify(rows);
                },
                handleSaveAll() {
                    console.log('[Alpine] handleSaveAll triggered', {
                        logsCount: this.logs.length,
                        selectedDeviceId: this.selectedDeviceId,
                        hasWire: !!this.$wire
                    });
                    if (!this.logs.length) {
                        console.warn('[Alpine] handleSaveAll: no logs');
                        alert('No logs to save');
                        return;
                    }
                    // Ensure device ID is set in each log
                    const logsToSave = this.logs.map(log => ({
                        ...log,
                        deviceId: log.deviceId || this.selectedDeviceId || 1
                    }));
                    console.log('[Alpine] Calling Livewire doSaveAll with:', logsToSave.length, 'logs');
                    this.$wire.call('doSaveAll', logsToSave)
                        .then(res => {
                            console.log('[Alpine] doSaveAll completed');
                            this.selected.clear();
                        })
                        .catch(err => {
                            console.error('[Alpine] doSaveAll error:', err);
                            alert('Error saving logs: ' + (err?.message || err));
                        });
                },
                handleSaveSelected() {
                    const rows = Array.from(this.selected).map(i => this.logs[i]).filter(Boolean);
                    console.log('[Alpine] handleSaveSelected triggered', {
                        selectedCount: this.selected.size,
                        rowsCount: rows.length,
                        selectedDeviceId: this.selectedDeviceId,
                        hasWire: !!this.$wire
                    });
                    if (!rows.length) {
                        console.warn('[Alpine] handleSaveSelected: no rows selected');
                        alert('No rows selected');
                        return;
                    }
                    // Ensure device ID is set in each log
                    const logsToSave = rows.map(log => ({
                        ...log,
                        deviceId: log.deviceId || this.selectedDeviceId || 1
                    }));
                    console.log('[Alpine] Calling Livewire doSaveSelected with:', logsToSave.length, 'logs');
                    this.$wire.call('doSaveSelected', logsToSave)
                        .then(res => {
                            console.log('[Alpine] doSaveSelected completed');
                            this.selected.clear();
                        })
                        .catch(err => {
                            console.error('[Alpine] doSaveSelected error:', err);
                            alert('Error saving logs: ' + (err?.message || err));
                        });
                },
                getDeviceName(deviceId) {
                    if (!deviceId) return 'No device';
                    const device = this.devices.find(d => d.id == deviceId);
                    return device ? device.name : 'Unknown device';
                },
                prepareSaveAll() {
                    console.log('[Alpine] prepareSaveAll triggered', this.logs.length);
                    if (!this.logs.length) {
                        alert('No logs to save');
                        return;
                    }
                    // Prepare logs with device IDs
                    this.preparedLogs = this.logs.map(log => ({
                        ...log,
                        deviceId: log.deviceId || this.selectedDeviceId || 1
                    }));
                    console.log('[Alpine] Prepared', this.preparedLogs.length, 'logs for saving');
                },
                prepareSaveSelected() {
                    const rows = Array.from(this.selected).map(i => this.logs[i]).filter(Boolean);
                    console.log('[Alpine] prepareSaveSelected triggered', rows.length);
                    if (!rows.length) {
                        alert('No rows selected');
                        return;
                    }
                    // Prepare logs with device IDs
                    this.preparedLogs = rows.map(log => ({
                        ...log,
                        deviceId: log.deviceId || this.selectedDeviceId || 1
                    }));
                    console.log('[Alpine] Prepared', this.preparedLogs.length, 'selected logs for saving');
                },
                saveLogs(logs, deviceId) {
                    console.log('[Simple] saveLogs called with', logs.length, 'logs, deviceId:', deviceId);
                    if (!logs.length) {
                        alert('No logs to save');
                        return;
                    }
                    
                    const logsToSave = logs.map(log => ({
                        badge: log.badge || log.badgeNumber || log.badge_number,
                        logged_at: log.logged_at || log.logTime || log.log_time,
                        device_id: deviceId || 1
                    }));
                    
                    this.postToServer(logsToSave);
                },
                
                saveSelected() {
                    console.log('[Simple] saveSelected called with', this.selected.size, 'selected rows');
                    const rows = Array.from(this.selected).map(i => this.logs[i]).filter(Boolean);
                    if (!rows.length) {
                        alert('No rows selected');
                        return;
                    }
                    
                    const logsToSave = rows.map(log => ({
                        badge: log.badge || log.badgeNumber || log.badge_number,
                        logged_at: log.logged_at || log.logTime || log.log_time,
                        device_id: 1
                    }));
                    
                    this.postToServer(logsToSave);
                },
                
                postToServer(logs) {
                    console.log('[Simple] postToServer - sending', logs.length, 'logs');
                    this.saving = true;
                    this.parsed = 0;
                    this.total = logs.length;
                    
                    window.dispatchEvent(new CustomEvent('import-saving-start', { detail: { total: logs.length } }));
                    
                    const startTime = Date.now();
                    let estimatedDuration = Math.max(5000, this.total * 5); // Estimate 5ms per record, min 5 seconds
                    let maxProgressBeforeComplete = Math.max(95, this.total - 100); // Cap at 95% or total-100, whichever is higher
                    
                    // Simulate progress during save - increment gradually but cap before server response
                    const progressInterval = setInterval(() => {
                        const elapsed = Date.now() - startTime;
                        const estimatedProgress = (elapsed / estimatedDuration) * this.total;
                        
                        // Cap progress at maxProgressBeforeComplete to avoid reaching 100% before server finishes
                        const cappedProgress = Math.min(estimatedProgress, maxProgressBeforeComplete);
                        
                        if (this.parsed < cappedProgress) {
                            this.parsed = Math.max(this.parsed, Math.floor(cappedProgress));
                            window.dispatchEvent(new CustomEvent('import-saving-progress', { 
                                detail: { processed: this.parsed, total: this.total } 
                            }));
                        }
                    }, 100);
                    
                    fetch('/attendance-logs/save', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({ logs: logs })
                    })
                    .then(response => {
                        clearInterval(progressInterval);
                        console.log('[Simple] Server response status:', response.status);
                        if (!response.ok) throw new Error('HTTP ' + response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('[Simple] Server response data:', data);
                        this.parsed = logs.length;
                        window.dispatchEvent(new CustomEvent('import-saving-progress', { 
                            detail: { processed: this.parsed, total: this.total } 
                        }));
                        
                        // Mark logs as stored by badge match
                        const savedBadges = data.saved_badges || [];
                        for (let i = 0; i < this.logs.length; i++) {
                            if (savedBadges.includes(this.logs[i].badge)) {
                                this.stored.add(i);
                            }
                        }
                        
                        window.dispatchEvent(new CustomEvent('import-saving-complete', { 
                            detail: { 
                                saved: data.saved || logs.length, 
                                skipped: data.skipped || 0,
                                message: data.message || 'Saved successfully'
                            } 
                        }));
                    })
                    .catch(error => {
                        console.error('[Simple] Error saving logs:', error);
                        clearInterval(progressInterval);
                        this.saving = false;
                        alert('Error: ' + error.message);
                    });
                },
                // Pagination helpers
                totalPages() { return Math.max(1, Math.ceil(this.filteredLogs().length / this.perPage || 1)); },
                pageStart() { return this.filteredLogs().length ? ((this.currentPage - 1) * this.perPage) + 1 : 0; },
                pageEnd() { return Math.min(this.filteredLogs().length, this.currentPage * this.perPage); },
                paged() {
                    const filtered = this.filteredLogs();
                    const start = (this.currentPage - 1) * this.perPage;
                    return filtered.slice(start, start + this.perPage);
                },
                pageIndex(idx) { 
                    const filtered = this.filteredLogs();
                    const start = (this.currentPage - 1) * this.perPage;
                    const row = filtered[start + idx];
                    return this.logs.indexOf(row);
                },
                nextPage() { if (this.currentPage < this.totalPages()) this.currentPage++; },
                prevPage() { if (this.currentPage > 1) this.currentPage--; }
            }
        }
    </script>
</div>
