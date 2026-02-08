<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::component('attendance-logs-import', \App\Livewire\AttendanceLogsImport::class);
        Livewire::component('attendance-logs-bulk-upload', \App\Livewire\AttendanceLogsBulkUpload::class);
        Blade::component('layouts.admin', 'admin-layout');
    }
}
