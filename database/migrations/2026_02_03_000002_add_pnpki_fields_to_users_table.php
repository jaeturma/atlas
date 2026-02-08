<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pnpki_full_name')->nullable();
            $table->string('pnpki_serial_number')->nullable();
            $table->string('pnpki_certificate_path')->nullable();
            $table->date('pnpki_valid_until')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'pnpki_full_name',
                'pnpki_serial_number',
                'pnpki_certificate_path',
                'pnpki_valid_until',
            ]);
        });
    }
};
