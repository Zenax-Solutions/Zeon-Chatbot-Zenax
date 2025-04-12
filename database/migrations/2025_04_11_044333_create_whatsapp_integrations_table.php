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
        Schema::create('whatsapp_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_bot_id')->constrained('chat_bots')->onDelete('cascade');
            $table->string('whatsapp_token')->nullable();
            $table->string('whatsapp_phone_number_id')->nullable();
            $table->string('whatsapp_verify_token')->nullable();
            $table->string('webhook_url')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_integrations');
    }
};
