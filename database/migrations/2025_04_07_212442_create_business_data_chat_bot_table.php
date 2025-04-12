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
        Schema::create('business_data_chat_bot', function (Blueprint $table) {
            $table->unsignedBigInteger('chat_bot_id');
            $table->unsignedBigInteger('business_data_id');

            $table->foreign('chat_bot_id')->references('id')->on('chat_bots')->onDelete('cascade');
            $table->foreign('business_data_id')->references('id')->on('business_data')->onDelete('cascade');

            $table->primary(['chat_bot_id', 'business_data_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_data_chat_bot');
    }
};
