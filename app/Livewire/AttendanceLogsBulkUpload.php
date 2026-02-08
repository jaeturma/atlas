<?php

namespace App\Livewire;

use App\Models\AttendanceLog;
use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AttendanceLogsBulkUpload extends Component
{
    public $deviceId = 1;
    public $startDate;
    public $endDate;
    public $uploading = false;
    public $progress = 0;
    public $totalRecords = 0;
    public $savedRecords = 0;
    public $skippedRecords = 0;
    public $uploadedFileName = '';
    public $logs = [];

    public function mount()
    {
        $today = Carbon::now();
        $this->startDate = $today->copy()->startOfMonth()->toDateString();
        $this->endDate = $today->toDateString();
    }

    public function render()
    {
        return view('livewire.attendance-logs-bulk-upload', [
            'devices' => Device::orderBy('name')->get(),
        ]);
    }

    public function extractFromPath($filePath)
    {
        if (!$filePath) {
            session()->flash('error', 'File path is empty');
            return;
        }
        
        $this->handleFileUpload($filePath);
    }

    #[\Livewire\Attributes\On('file-uploaded')]
    public function onFileUploaded($filePath)
    {
        \Log::info('onFileUploaded listener triggered', ['filePath' => $filePath]);
        $this->extractFromPath($filePath);
    }

    /**
     * Called when file is received from the form
     */
    public function handleFileUpload($filePath)
    {
        \Log::info('handleFileUpload called', ['filePath' => $filePath]);
        
        $this->uploadedFileName = '';
        $this->totalRecords = 0;
        $this->logs = [];

        try {
            if (empty($filePath)) {
                throw new \Exception('File path is empty');
            }
            
            if (!file_exists($filePath)) {
                throw new \Exception('File not found at: ' . $filePath);
            }

            $content = file_get_contents($filePath);
            if ($content === false) {
                throw new \Exception('Could not read file');
            }

            \Log::info('File content read', [
                'size' => strlen($content),
                'lines' => count(explode("\n", $content)),
            ]);

            $lines = array_filter(array_map('trim', explode("\n", $content)));

            \Log::info('Processing lines', [
                'lineCount' => count($lines),
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
            ]);

            foreach ($lines as $line) {
                if (empty($line)) continue;

                // Try to parse as CSV first
                $row = str_getcsv($line);
                
                // Support multiple formats:
                // CSV: badge_number, log_datetime
                // Log format: badge_number, 2024-01-15 09:30:00
                $badge = trim($row[0] ?? '');
                $datetime = trim($row[1] ?? '');

                if (empty($badge) || empty($datetime)) continue;

                try {
                    $logDateTime = Carbon::parse($datetime);
                    
                    // Check if within date range
                    if ($logDateTime->between(
                        Carbon::parse($this->startDate),
                        Carbon::parse($this->endDate)->endOfDay()
                    )) {
                        $this->logs[] = [
                            'badge_number' => $badge,
                            'log_datetime' => $logDateTime,
                        ];
                        $this->totalRecords++;
                    }
                } catch (\Throwable $e) {
                    // Skip lines that can't be parsed
                    \Log::debug('Skipping unparseable line', ['line' => $line, 'error' => $e->getMessage()]);
                    continue;
                }
            }

            $this->uploadedFileName = basename($filePath);
            \Log::info('File extraction complete', [
                'totalRecords' => $this->totalRecords,
                'fileName' => $this->uploadedFileName,
            ]);
            
            session()->flash('success', "Extracted {$this->totalRecords} records from {$this->uploadedFileName}");
        } catch (\Throwable $e) {
            \Log::error('File processing error', ['error' => $e->getMessage()]);
            session()->flash('error', 'Error processing file: ' . $e->getMessage());
        }
    }

    public function uploadLogs()
    {
        \Log::info('uploadLogs called', [
            'logsCount' => count($this->logs),
            'totalRecords' => $this->totalRecords,
        ]);
        
        if (empty($this->logs)) {
            session()->flash('error', 'No logs to upload. Please extract logs first.');
            return;
        }

        // Device is auto-set to 1 (USB)
        $this->uploading = true;
        $this->savedRecords = 0;
        $this->skippedRecords = 0;
        $totalRecords = count($this->logs);

        DB::beginTransaction();
        try {
            foreach ($this->logs as $index => $log) {
                try {
                    $created = AttendanceLog::firstOrCreate(
                        [
                            'device_id' => $this->deviceId,
                            'badge_number' => $log['badge_number'],
                            'log_datetime' => $log['log_datetime'],
                        ],
                        [
                            'status' => null,
                            'punch_type' => null,
                        ]
                    );

                    if ($created->wasRecentlyCreated) {
                        $this->savedRecords++;
                    } else {
                        $this->skippedRecords++;
                    }
                } catch (\Throwable $e) {
                    \Log::error('Error saving log: ' . $e->getMessage());
                    $this->skippedRecords++;
                }

                // Update progress
                $this->progress = (int)(($index + 1) / $totalRecords * 100);
                $this->dispatch('upload-progress', [
                    'progress' => $this->progress,
                    'saved' => $this->savedRecords,
                    'skipped' => $this->skippedRecords,
                    'total' => $totalRecords,
                ]);
            }

            DB::commit();
            $this->uploading = false;
            $this->logs = [];
            $this->uploadedFileName = '';
            session()->flash('success', "Successfully uploaded {$this->savedRecords} records. Skipped {$this->skippedRecords} duplicates.");
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->uploading = false;
            \Log::error('Bulk upload error: ' . $e->getMessage());
            session()->flash('error', 'Error uploading logs: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->reset(['logs', 'totalRecords', 'savedRecords', 'skippedRecords', 'progress', 'uploading', 'uploadedFileName']);
        $this->startDate = Carbon::now()->startOfMonth()->toDateString();
        $this->endDate = Carbon::now()->toDateString();
    }
}

