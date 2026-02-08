<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Device;
use App\Services\ZKTecoService;
use Livewire\Attributes\Validate;

class DeviceForm extends Component
{
    public ?Device $device = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:255')]
    public ?string $model = null;

    #[Validate('nullable|string|max:255')]
    public ?string $serial_number = null;

    #[Validate('required|ip')]
    public string $ip_address = '';

    #[Validate('required|integer|min:1|max:65535')]
    public int $port = 4370;

    #[Validate('nullable|string|max:255')]
    public ?string $location = null;

    #[Validate('boolean')]
    public bool $is_active = true;

    public bool $testing = false;
    public ?string $testMessage = null;
    public bool $testSuccess = false;

    public function mount(?Device $device = null)
    {
        if ($device) {
            $this->device = $device;
            $this->name = $device->name ?? '';
            $this->model = $device->model;
            $this->serial_number = $device->serial_number;
            $this->ip_address = $device->ip_address ?? '';
            $this->port = $device->port ?? 4370;
            $this->location = $device->location;
            $this->is_active = $device->is_active ?? true;
        }
    }

    public function testConnection()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
        ]);

        $this->testing = true;
        $this->testMessage = null;
        $this->testSuccess = false;

        try {
            // Create a temporary device object for testing
            $tempDevice = new Device([
                'ip_address' => $this->ip_address,
                'port' => $this->port,
            ]);

            $service = new ZKTecoService($tempDevice);
            $result = $service->testConnection();

            $this->testSuccess = $result['success'];
            $this->testMessage = $result['message'];

            if ($result['success'] && isset($result['device'])) {
                $this->testMessage .= " | Version: {$result['device']['version']}, Users: {$result['device']['users']}, Logs: {$result['device']['logs']}";
            }
        } catch (\Exception $e) {
            $this->testSuccess = false;
            $this->testMessage = 'Error: ' . $e->getMessage();
        } finally {
            $this->testing = false;
        }
    }

    public function render()
    {
        return view('livewire.device-form');
    }
}
