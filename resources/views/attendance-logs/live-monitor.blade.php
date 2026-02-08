<x-admin-layout>
    {{-- No sidebar/header title --}}
    <div class="py-8">
        <div class="w-full mx-auto px-4 lg:px-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">Live Attendance Monitor</h1>
            <p class="text-gray-600 mt-1">Real-time attendance log streaming</p>
        </div>
        <div class="flex gap-2">
            <button onclick="toggleAutoRefresh()" id="refresh-btn" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                ⚡ Auto-Refresh: ON
            </button>
            <button onclick="clearLogs()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                🗑️ Clear Feed
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 border border-gray-400">
            <div class="text-gray-600 text-sm">Total Today</div>
            <div id="total-count" class="text-3xl font-bold mt-2">0</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border border-gray-400">
            <div class="text-gray-600 text-sm">Check Ins</div>
            <div id="checkin-count" class="text-3xl font-bold text-green-600 mt-2">0</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border border-gray-400">
            <div class="text-gray-600 text-sm">Check Outs</div>
            <div id="checkout-count" class="text-3xl font-bold text-red-600 mt-2">0</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border border-gray-400">
            <div class="text-gray-600 text-sm">Last Sync</div>
            <div id="last-sync" class="text-lg font-bold mt-2">--:--:--</div>
            <div id="sync-status" class="text-xs mt-1 text-gray-500"></div>
        </div>
    </div>

    <!-- Filters / Connection -->
    <div class="bg-white rounded-lg shadow p-4 mb-6 border border-gray-400">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Select Device</label>
                <select id="device-filter" class="w-full border rounded px-3 py-2" onchange="onDeviceChange()">
                    <option value="">-- Choose a device --</option>
                    @foreach($devices as $device)
                        <option value="{{ $device->id }}" data-live-mode="{{ $device->live_sync_mode ?? 'zk' }}">{{ $device->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Refresh Rate</label>
                <select id="refresh-rate" onchange="changeRefreshRate()" class="w-full border rounded px-3 py-2">
                    <option value="3">3 seconds</option>
                    <option value="5" selected>5 seconds</option>
                    <option value="10">10 seconds</option>
                    <option value="30">30 seconds</option>
                    <option value="60">60 seconds (1 min)</option>
                    <option value="180">3 minutes</option>
                    <option value="300">5 minutes</option>
                    <option value="900">15 minutes</option>
                    <option value="1800">30 minutes</option>
                    <option value="3600">1 hour</option>
                    <option value="21600">6 hours</option>
                    <option value="43200">12 hours</option>
                    <option value="86400">1 day (24 hrs)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Sync Mode</label>
                <select id="sync-mode" onchange="changeSyncMode()" class="w-full border rounded px-3 py-2">
                    <option value="zk">UDP (ZKEM)</option>
                    <option value="auto">Auto (ADMS/ZKEM)</option>
                </select>
            </div>
            <div class="flex items-end gap-3">
                <button type="button" id="connect-btn" class="w-full bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded flex items-center justify-center gap-2" style="pointer-events: auto !important; cursor: pointer !important;">
                    <i class="fas fa-plug"></i> Connect
                </button>
                <button type="button" onclick="disconnectDevice()" class="w-full bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded flex items-center justify-center gap-2" id="disconnect-btn" disabled style="opacity: 0.5; cursor: not-allowed;">
                    <i class="fas fa-plug-circle-xmark"></i> Disconnect
                </button>
            </div>
            <div class="flex items-end">
                <div class="w-full flex items-center gap-2 bg-gray-50 border rounded px-3 py-2">
                    <div id="status-indicator" class="w-3 h-3 bg-red-500 rounded-full"></div>
                    <div id="status-text" class="text-sm font-semibold text-red-600">Disconnected</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid: Device Status, Live Feed, Activity Log -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        <!-- Device Status Panel -->
        <div class="bg-white rounded-lg shadow p-6 border border-gray-400">
            <h3 class="text-lg font-bold mb-4">Device Status</h3>
            <div id="device-status-container" class="space-y-4">
                <div class="p-4 bg-gray-50 rounded border border-gray-200">
                    <p class="text-center text-gray-500">Loading device status...</p>
                </div>
            </div>
        </div>

        <!-- Live Feed -->
        <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-400">
            <div class="bg-gray-100 px-6 py-3 border-b font-semibold flex items-center gap-2">
                <div id="live-indicator" class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                LIVE FEED
                <span id="log-count" class="ml-auto text-sm text-gray-600">(0 logs)</span>
            </div>
            
            <div id="logs-container" class="divide-y max-h-96 overflow-y-auto">
                <div class="p-8 text-center text-gray-500">
                    Waiting for attendance records...
                </div>
            </div>
        </div>

        <!-- Activity Log -->
        <div class="bg-white rounded-lg shadow p-6 border border-gray-400">
            <h3 class="text-lg font-bold mb-4">Activity Log</h3>
            <div id="activity-log" class="text-sm space-y-2 max-h-96 overflow-y-auto">
                <div class="text-gray-500">No activity yet</div>
            </div>
        </div>
    </div>
</div>

<script>
let autoRefreshEnabled = true;
let refreshInterval = 5000; // 5 seconds default
let refreshTimer = null;
let allLogs = [];
let previousLogIds = new Set(); // Track previously seen log IDs
let lastRefreshTime = null;
let selectedDeviceId = '';
let isConnected = false;
let syncMode = 'zk'; // 'zk' for UDP (ZKEM), 'auto' for multiprotocol
let shouldSyncNextFetch = false;

function changeSyncMode() {
    const sel = document.getElementById('sync-mode');
    syncMode = sel?.value || 'zk';
    // Persist to backend if a device is selected
    if (selectedDeviceId && syncMode) {
        fetch(`/devices/${selectedDeviceId}/sync-mode`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
            },
            body: JSON.stringify({ sync_mode: syncMode })
        }).then(r => r.json()).then(res => {
            if (res?.success) {
                const modeText = syncMode === 'auto' ? 'Auto (ADMS/ZKEM)' : 'UDP (ZKEM)';
                addActivityLog('Saved sync mode: ' + modeText + ' for device');
            }
        }).catch(() => {});
    }
}

// Build a YYYY-MM-DD string in local time (avoid UTC shift/toISOString rollback)
function formatLocalDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return year + '-' + month + '-' + day;
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    console.log('Live Monitor initialized');
    updateConnectionStatus(false, 'Select a device to connect', 'down');
    const deviceSelect = document.getElementById('device-filter');
    const connectBtn = document.getElementById('connect-btn');
    
    // Add direct event listener to Connect button as backup
    if (connectBtn) {
        connectBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('[Connect Button Click Event] Triggered!');
            connectDevice();
        });
        console.log('Connect button found and listener attached');
    } else {
        console.error('Connect button NOT found!');
    }
    
    if (deviceSelect) {
        // Auto-select first device if available
        if (deviceSelect.options.length > 1 && !deviceSelect.value) {
            deviceSelect.selectedIndex = 1; // Select first actual device (skip placeholder)
            console.log('Auto-selected first device, index:', deviceSelect.selectedIndex);
        }
        // Call handler to set initial state AND populate selectedDeviceId
        onDeviceChange();
        console.log('After onDeviceChange, selectedDeviceId:', selectedDeviceId);
    }
});

function onDeviceChange() {
    const select = document.getElementById('device-filter');
    selectedDeviceId = select?.value || '';
    console.log('[onDeviceChange] Called. Selected device ID:', selectedDeviceId);
    
    // Apply saved sync mode for this device (default 'zk')
    const savedMode = select?.selectedOptions[0]?.getAttribute('data-live-mode') || 'zk';
    const syncModeSel = document.getElementById('sync-mode');
    if (syncModeSel) {
        syncModeSel.value = savedMode;
    }
    syncMode = savedMode;
    console.log('[onDeviceChange] Sync mode set to:', syncMode);
    
    const connectBtn = document.getElementById('connect-btn');
    const disconnectBtn = document.getElementById('disconnect-btn');
    console.log('[onDeviceChange] Connect button found:', !!connectBtn);
    
    // Stop any existing connection
    if (isConnected) {
        isConnected = false;
        if (refreshTimer) clearInterval(refreshTimer);
    }
    
    // Enable Connect button if device selected, disable Disconnect
    if (connectBtn) {
        const shouldEnable = !!selectedDeviceId;
        console.log('[onDeviceChange] Should enable Connect button:', shouldEnable);
        
        if (shouldEnable) {
            connectBtn.disabled = false;
            connectBtn.removeAttribute('disabled');
            connectBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            connectBtn.disabled = true;
            connectBtn.setAttribute('disabled', 'disabled');
        }
        connectBtn.innerHTML = '<i class="fas fa-plug"></i> Connect';
        
        // Verify final state
        console.log('[onDeviceChange] Connect button disabled attr:', connectBtn.getAttribute('disabled'));
        console.log('[onDeviceChange] Connect button disabled prop:', connectBtn.disabled);
    }
    if (disconnectBtn) {
        disconnectBtn.disabled = true;
    }
    
    updateConnectionStatus(false, selectedDeviceId ? 'Click Connect to start' : 'Select a device to connect', 'down');
    resetLiveView();
}

function connectDevice() {
    console.log('[connectDevice] Called. selectedDeviceId:', selectedDeviceId);
    
    if (!selectedDeviceId) {
        alert('Please select a device first.');
        console.log('[connectDevice] No device selected, aborting');
        return;
    }
    
    const connectBtn = document.getElementById('connect-btn');
    const disconnectBtn = document.getElementById('disconnect-btn');
    const deviceName = document.getElementById('device-filter')?.selectedOptions[0]?.textContent || 'device';
    
    console.log('[connectDevice] Device name:', deviceName);
    
    // Show loading animation on Connect button
    if (connectBtn) {
        connectBtn.disabled = true;
        connectBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Connecting...';
    }
    
    updateConnectionStatus(true, 'Connecting...', 'warn');
    addActivityLog('Connecting to ' + deviceName);
    const modeText = syncMode === 'auto' ? 'Auto (ADMS/ZKEM)' : 'UDP (ZKEM)';
    addActivityLog('Sync mode: ' + modeText);
    shouldSyncNextFetch = true;
    
    // Simulate connection delay and fetch logs
    fetchLogs().then(() => {
        // Connection successful
        isConnected = true;
        
        // Disable Connect button, enable Disconnect button
        if (connectBtn) {
            connectBtn.disabled = true;
            connectBtn.innerHTML = '<i class="fas fa-check"></i> Connected';
            connectBtn.className = 'w-full bg-green-800 hover:bg-green-700 text-white px-4 py-2 rounded flex items-center justify-center gap-2';
        }
        if (disconnectBtn) {
            disconnectBtn.disabled = false;
            disconnectBtn.removeAttribute('disabled');
            disconnectBtn.style.opacity = '1';
            disconnectBtn.style.cursor = 'pointer';
        }
        
        updateConnectionStatus(true, 'Connected', 'ok');
        addActivityLog('Connected to ' + deviceName);
        startAutoRefresh();
    }).catch(() => {
        // Connection failed - re-enable Connect button
        if (connectBtn) {
            connectBtn.disabled = false;
            connectBtn.innerHTML = '<i class="fas fa-plug"></i> Connect';
            connectBtn.className = 'w-full bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded flex items-center justify-center gap-2';
        }
        updateConnectionStatus(false, 'Connection failed', 'down');
        addActivityLog('Failed to connect to ' + deviceName);
    });
}

function disconnectDevice() {
    const connectBtn = document.getElementById('connect-btn');
    const disconnectBtn = document.getElementById('disconnect-btn');
    
    isConnected = false;
    
    // Stop auto-refresh
    if (refreshTimer) clearInterval(refreshTimer);
    
    // Enable Connect button, disable Disconnect button
    if (connectBtn) {
        connectBtn.disabled = false;
        connectBtn.innerHTML = '<i class="fas fa-plug"></i> Connect';
        connectBtn.className = 'w-full bg-blue-900 hover:bg-blue-800 text-white px-4 py-2 rounded flex items-center justify-center gap-2';
        connectBtn.style.pointerEvents = 'auto';
        connectBtn.style.cursor = 'pointer';
    }
    if (disconnectBtn) {
        disconnectBtn.disabled = true;
        disconnectBtn.setAttribute('disabled', 'disabled');
        disconnectBtn.style.opacity = '0.5';
        disconnectBtn.style.cursor = 'not-allowed';
    }
    
    updateConnectionStatus(false, 'Disconnected', 'down');
    resetLiveView();
    addActivityLog('Disconnected from device');
}

function startAutoRefresh() {
    if (refreshTimer) clearInterval(refreshTimer);
    
    refreshTimer = setInterval(() => {
        if (autoRefreshEnabled && isConnected && selectedDeviceId) {
            fetchLogs().catch(() => {
                // Ignore errors during auto-refresh, just log them
            });
        }
    }, refreshInterval);
}

function changeRefreshRate() {
    const rate = parseInt(document.getElementById('refresh-rate').value);
    refreshInterval = rate * 1000;
    startAutoRefresh();
}

function toggleAutoRefresh() {
    autoRefreshEnabled = !autoRefreshEnabled;
    const btn = document.getElementById('refresh-btn');
    
    if (autoRefreshEnabled) {
        btn.className = 'bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded';
        btn.textContent = '⚡ Auto-Refresh: ON';
        startAutoRefresh();
    } else {
        btn.className = 'bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded';
        btn.textContent = '⏸️ Auto-Refresh: OFF';
        clearInterval(refreshTimer);
    }
}

function manualRefresh() {
    fetchLogs();
}

function fetchLogs() {
    if (!selectedDeviceId) return Promise.reject('No device selected');
    shouldSyncNextFetch = true;
    // Show syncing indicator
    const syncStatusEl = document.getElementById('sync-status');
    if (syncStatusEl) {
        if (shouldSyncNextFetch) {
            syncStatusEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';
            syncStatusEl.className = 'text-xs mt-1 text-blue-600';
        } else {
            syncStatusEl.innerHTML = '<i class="fas fa-rotate fa-spin"></i> Refreshing...';
            syncStatusEl.className = 'text-xs mt-1 text-gray-500';
        }
    }


    const url = new URL('{{ route("attendance-logs.live-feed") }}', window.location.origin);
    url.searchParams.set('device_id', selectedDeviceId);
    url.searchParams.set('sync_mode', syncMode);
    url.searchParams.set('sync', '1');
    url.searchParams.set('window_seconds', Math.max(10, Math.floor(refreshInterval / 1000)));

    return fetch(url.toString(), {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => {
        console.log('Response received:', response.ok);
        if (!response.ok) throw new Error('Failed to fetch logs');
        return response.json();
    })
    .then(data => {
        console.log('Data received:', data);
        console.log('Sync performed:', data.sync_performed, 'Message:', data.sync_message);
        console.log('Sync mode:', data.sync_mode);
        console.log('Logs returned from backend:', data.logs ? data.logs.length : 0);
        
        // Detect new logs
        const newLogs = [];
        if (data.logs && data.logs.length > 0) {
            data.logs.forEach(log => {
                if (!previousLogIds.has(log.id)) {
                    newLogs.push(log);
                    previousLogIds.add(log.id);
                }
            });
        }
        
        // Track protocol information
        const protocols = {};
        if (data.logs && data.logs.length > 0) {
            data.logs.forEach(log => {
                if (log.device_protocol) {
                    if (!protocols[log.device_protocol]) {
                        protocols[log.device_protocol] = 0;
                    }
                    protocols[log.device_protocol]++;
                }
            });
        }
        
        allLogs = data.logs || [];
        lastRefreshTime = new Date();
        
        console.log('Logs count:', allLogs.length);
        console.log('New logs:', newLogs.length);
        console.log('Sync performed:', data.sync_performed);
        console.log('Sync message:', data.sync_message);
        console.log('Protocols active:', protocols);
        console.log('Device info:', data.devices);
        
        renderLogs();
        updateStats();
        updateConnectionStatus(true, 'Connected', 'ok');
        updateDeviceStatus(data.devices || {});
        shouldSyncNextFetch = false;
        
        // Update sync status display
        const syncStatusEl = document.getElementById('sync-status');
        if (syncStatusEl) {
            if (data.sync_performed) {
                syncStatusEl.textContent = data.sync_message || 'Synced';
                syncStatusEl.className = 'text-xs mt-1 text-green-600';
                console.log('[SYNC] Device sync completed:', data.sync_message);
            } else {
                syncStatusEl.textContent = 'No sync';
                syncStatusEl.className = 'text-xs mt-1 text-gray-400';
                console.log('[SYNC] No sync performed');
            }
        }
        
        // Log new attendance records
        if (newLogs.length > 0) {
            console.log('[NEW LOGS DETECTED]', newLogs.length, 'new logs');
            newLogs.forEach(log => {
                const timeStr = log.log_datetime.split(' ')[1] || '';
                const timeParts = timeStr.split(':');
                let hours = parseInt(timeParts[0]);
                const minutes = timeParts[1];
                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12 || 12;
                const formattedTime = String(hours).padStart(2, '0') + ':' + minutes + ' ' + ampm;
                
                const statusIcon = log.status === 'In' ? '✓' : '✕';
                const statusText = log.status === 'In' ? 'Check In' : 'Check Out';
                addActivityLog(statusIcon + ' New ' + statusText + ': ' + log.badge_number + ' at ' + formattedTime);
            });
        }
        
        // Log protocol activity summary (only on first load or if explicitly synced)
        if (data.sync_performed) {
            const protocolMsg = Object.entries(protocols)
                .map(([proto, count]) => proto.toUpperCase() + ': ' + count)
                .join(' | ');
            if (protocolMsg) {
                addActivityLog('Synced logs - ' + protocolMsg);
            }
        }
    })
    .catch(error => {
        console.error('Error fetching logs:', error);
        if (isConnected) {
            // Only show warning if we're supposed to be connected
            updateConnectionStatus(true, 'Connection issue, retrying...', 'warn');
            addActivityLog('Connection issue: ' + error.message);
        }
        return Promise.reject(error);
    });
}

function renderLogs() {
    const container = document.getElementById('logs-container');
    const deviceFilter = document.getElementById('device-filter').value;
    
    let filteredLogs = allLogs;
    
    if (deviceFilter) {
        filteredLogs = filteredLogs.filter(log => log.device_id == deviceFilter);
    }
    
    // Sort by most recent first (using timestamp for comparison)
    filteredLogs = filteredLogs.sort((a, b) => b.log_timestamp - a.log_timestamp);
    
    if (filteredLogs.length === 0) {
        container.innerHTML = '<div class="p-8 text-center text-gray-500">No logs found</div>';
        document.getElementById('log-count').textContent = '(0 logs)';
        return;
    }
    
    const html = filteredLogs.map(log => {
        // Extract time directly from pre-formatted backend string (already in user timezone)
        // Format: "YYYY-MM-DD HH:MM:SS"
        const parts = log.log_datetime.split(' ');
        const dateStr = parts[0] || ''; // Get YYYY-MM-DD part
        const timeHMS = parts[1] || '00:00:00'; // Get HH:MM:SS part
        
        // Convert 24-hour time to 12-hour AM/PM format
        const timeParts = timeHMS.split(':');
        let hours = parseInt(timeParts[0]);
        const minutes = timeParts[1];
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12; // Convert 0 to 12 for midnight
        const timeStr = String(hours).padStart(2, '0') + ':' + minutes + ' ' + ampm;
        
        const statusClass = log.status === 'In' ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500';
        const statusBadgeClass = log.status === 'In' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
        
        // Protocol badge styling
        const protocolBadgeClass = log.device_protocol === 'adms' 
            ? 'bg-blue-100 text-blue-800' 
            : log.device_protocol === 'zkem'
            ? 'bg-purple-100 text-purple-800'
            : 'bg-gray-100 text-gray-800';
        
        const protocolLabel = log.device_protocol === 'adms' 
            ? '📡 ADMS' 
            : log.device_protocol === 'zkem'
            ? '📠 ZKEM'
            : '❓ Unknown';
        
        return `
            <div class="p-4 ${statusClass} hover:bg-opacity-50 transition">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="font-bold text-lg">${log.badge_number}</div>
                        <div class="text-sm text-gray-600">
                            ${log.device_name || 'Unknown Device'}
                            <span class="inline-block ml-2 px-2 py-1 rounded text-xs font-semibold ${protocolBadgeClass}">
                                ${protocolLabel}
                            </span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-mono font-bold text-lg">${timeStr}</div>
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold ${statusBadgeClass}">
                            ${log.status === 'In' ? '✓ Check In' : '✕ Check Out'}
                        </span>
                    </div>
                </div>
                <div class="text-xs text-gray-500 mt-2">
                    ${dateStr} • ${log.punch_type || 'Unknown'} • ${log.employee_name || 'Not Linked'}
                </div>
            </div>
        `;
    }).join('');
    
    container.innerHTML = html;
    document.getElementById('log-count').textContent = `(${filteredLogs.length} logs)`;
    
    // Add to activity log
    if (filteredLogs.length > 0) {
        addActivityLog('Updated with ' + filteredLogs.length + ' log(s)');
    }
}

function updateStats() {
    // Since all logs are already for today (filtered on backend), just use allLogs directly
    const todayLogs = allLogs;
    const checkIns = todayLogs.filter(log => log.status === 'In').length;
    const checkOuts = todayLogs.filter(log => log.status === 'Out').length;
    
    document.getElementById('total-count').textContent = todayLogs.length;
    document.getElementById('checkin-count').textContent = checkIns;
    document.getElementById('checkout-count').textContent = checkOuts;
    
    // Update last sync time
    if (lastRefreshTime) {
        const timeStr = lastRefreshTime.toLocaleTimeString();
        document.getElementById('last-sync').textContent = timeStr;
    }
}

function updateHourlyChart(logs) {
    // Hourly chart removed from UI - keeping function for compatibility
}

function filterLogs() {
    renderLogs();
}

function clearLogs() {
    if (confirm('Clear the live feed? This only affects the display, not the database.')) {
        allLogs = [];
        document.getElementById('logs-container').innerHTML = '<div class="p-8 text-center text-gray-500">Feed cleared</div>';
        document.getElementById('log-count').textContent = '(0 logs)';
        addActivityLog('Feed cleared by user');
    }
}

function resetLiveView() {
    // Clear live feed UI
    allLogs = [];
    previousLogIds.clear(); // Clear tracked log IDs
    document.getElementById('logs-container').innerHTML = '<div class="p-8 text-center text-gray-500">Waiting for device connection...</div>';
    document.getElementById('log-count').textContent = '(0 logs)';
    // Reset stats
    document.getElementById('total-count').textContent = '0';
    document.getElementById('checkin-count').textContent = '0';
    document.getElementById('checkout-count').textContent = '0';
    document.getElementById('last-sync').textContent = '--:--:--';
    // Device status placeholder
    const container = document.getElementById('device-status-container');
    container.innerHTML = '<div class="p-4 bg-gray-50 rounded border border-gray-200"><p class="text-center text-gray-500">Connect a device to see status</p></div>';
}

function updateConnectionStatus(connected, message = '', level = 'ok') {
    const indicator = document.getElementById('status-indicator');
    const statusText = document.getElementById('status-text');
    
    let indicatorClass = 'w-3 h-3 rounded-full';
    let textClass = 'font-bold';
    let text = message || (connected ? 'Connected' : 'Disconnected');

    if (!connected || level === 'down') {
        indicatorClass += ' bg-red-500';
        textClass += ' text-red-600';
        text = message || 'Disconnected';
    } else if (level === 'warn') {
        indicatorClass += ' bg-yellow-400 animate-pulse';
        textClass += ' text-yellow-700';
    } else {
        indicatorClass += ' bg-green-500 animate-pulse';
        textClass += ' text-green-600';
    }
    
    indicator.className = indicatorClass;
    statusText.textContent = text;
    statusText.className = textClass;
}

function addActivityLog(message) {
    const log = document.getElementById('activity-log');
    const time = new Date().toLocaleTimeString();
    const entry = document.createElement('div');
    entry.className = 'text-gray-700 border-l-2 border-blue-500 pl-2 py-1';
    entry.textContent = `[${time}] ${message}`;
    
    // Add icon based on message type
    if (message.includes('protocol') || message.includes('ADMS') || message.includes('ZKEM')) {
        entry.className = 'text-blue-700 border-l-2 border-blue-600 pl-2 py-1 font-semibold';
    } else if (message.includes('error') || message.includes('Error') || message.includes('Disconnected')) {
        entry.className = 'text-red-700 border-l-2 border-red-600 pl-2 py-1';
    } else if (message.includes('success') || message.includes('Connected')) {
        entry.className = 'text-green-700 border-l-2 border-green-600 pl-2 py-1';
    }
    
    log.insertBefore(entry, log.firstChild);
    
    // Keep only last 10 entries
    while (log.children.length > 10) {
        log.removeChild(log.lastChild);
    }
}

function updateDeviceStatus(devices) {
    const container = document.getElementById('device-status-container');
    
    if (!devices || Object.keys(devices).length === 0) {
        container.innerHTML = '<div class="p-4 bg-gray-50 rounded border border-gray-200"><p class="text-center text-gray-500">No devices configured</p></div>';
        return;
    }
    
    let html = '';
    let connectedCount = 0;
    
    for (const [deviceId, deviceInfo] of Object.entries(devices)) {
        // Skip inactive/disconnected devices
        if (!deviceInfo.is_connected) {
            continue;
        }
        
        connectedCount++;
        const statusIcon = '✅';
        const statusColor = 'bg-green-50 border-green-200';
        const statusText = 'Online';
        const protocolIcon = deviceInfo.protocol === 'adms' ? '📡' : '📠';
        const statusLabel = deviceInfo.status === 'online_protocol_ok' ? 'Protocol OK' : 
                           deviceInfo.status === 'online_no_protocol' ? 'No Protocol' :
                           'Reachable';
        
        // Convert last_sync to 12-hour format with AM/PM
        let lastSyncFormatted = deviceInfo.last_sync;
        if (deviceInfo.last_sync) {
            const syncParts = deviceInfo.last_sync.split(' ');
            if (syncParts.length === 2) {
                const timeParts = syncParts[1].split(':');
                let hours = parseInt(timeParts[0]);
                const minutes = timeParts[1];
                const seconds = timeParts[2];
                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12 || 12;
                lastSyncFormatted = syncParts[0] + ' ' + String(hours).padStart(2, '0') + ':' + minutes + ':' + seconds + ' ' + ampm;
            }
        }
        
        html += `
            <div class="p-4 rounded border ${statusColor}">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <div class="font-bold text-lg">${statusIcon} ${deviceInfo.name}</div>
                        <div class="text-sm text-gray-600">${deviceInfo.ip_address}:${deviceInfo.port}</div>
                    </div>
                    <button onclick="syncDeviceNow('${deviceId}')" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                        Sync Now
                    </button>
                </div>
                <div class="text-xs space-y-1">
                    <div><strong>Status:</strong> ${statusText}</div>
                    <div><strong>Protocol:</strong> ${protocolIcon} ${deviceInfo.protocol.toUpperCase()}</div>
                    <div><strong>Details:</strong> ${statusLabel}</div>
                    <div><strong>Last Sync:</strong> ${lastSyncFormatted}</div>
                </div>
            </div>
        `;
    }
    
    if (connectedCount === 0) {
        container.innerHTML = '<div class="p-4 bg-gray-50 rounded border border-gray-200"><p class="text-center text-gray-500">No connected devices</p></div>';
    } else {
        container.innerHTML = html;
    }
}

function syncDeviceNow(deviceId) {
    const today = new Date();
    const todayStr = formatLocalDate(today);
    const endDate = formatLocalDate(today);
    
    if (!confirm('Sync attendance logs from this device?')) {
        return;
    }
    
    // Disable button during sync
    event.target.disabled = true;
    event.target.textContent = 'Syncing...';
    
    fetch(`/attendance-logs/sync/${deviceId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            start_date: todayStr,
            end_date: endDate,
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            addActivityLog('✅ Sync successful: ' + data.logs_count + ' new logs from ' + data.device_name);
            manualRefresh();
        } else {
            addActivityLog('❌ Sync failed: ' + data.message);
        }
    })
    .catch(error => {
        addActivityLog('❌ Sync error: ' + error.message);
    })
    .finally(() => {
        event.target.disabled = false;
        event.target.textContent = 'Sync Now';
    });
}

</script>

<style>
#logs-container {
    scroll-behavior: smooth;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
    </div>
</div>
</div>
</x-admin-layout>

