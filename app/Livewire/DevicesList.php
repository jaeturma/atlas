<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Device;
use Livewire\Attributes\On;

class DevicesList extends Component
{

    #[On('device-deleted')]
    public function refresh()
    {
        $this->resetPage();
    }

    public function render()
    {
        $devices = Device::all();

        return view('livewire.devices-list', compact('devices'));
    }
}
