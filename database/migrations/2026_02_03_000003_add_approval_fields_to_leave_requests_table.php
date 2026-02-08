<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('approved_pnpki_full_name')->nullable();
            $table->string('approved_pnpki_serial_number')->nullable();
            $table->string('approved_pnpki_certificate_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['approved_by_user_id']);
            $table->dropColumn([
                'approved_by_user_id',
                'approved_at',
                'approved_pnpki_full_name',
                'approved_pnpki_serial_number',
                'approved_pnpki_certificate_path',
            ]);
        });
    }
};
