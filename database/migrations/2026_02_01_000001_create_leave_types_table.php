<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->decimal('default_days', 6, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('leave_types')->insert([
            ['name' => 'Vacation Leave', 'code' => 'VL', 'default_days' => 15],
            ['name' => 'Sick Leave', 'code' => 'SL', 'default_days' => 15],
            ['name' => 'Special Leave', 'code' => 'SPL', 'default_days' => 3],
            ['name' => 'Force Leave', 'code' => 'FL', 'default_days' => 5],
            ['name' => 'Compensatory Time Off', 'code' => 'CTO', 'default_days' => 0],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
