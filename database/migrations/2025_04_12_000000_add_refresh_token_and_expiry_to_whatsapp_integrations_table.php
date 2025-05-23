<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('whatsapp_integrations', function (Blueprint $table) {
            $table->string('refresh_token')->nullable()->after('whatsapp_token');
            $table->timestamp('token_expires_at')->nullable()->after('refresh_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_integrations', function (Blueprint $table) {
            $table->dropColumn('refresh_token');
            $table->dropColumn('token_expires_at');
        });
    }
};
