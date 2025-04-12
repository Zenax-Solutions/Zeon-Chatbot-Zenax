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
        Schema::table('chat_bots', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('business_data_id')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('business_data_id')->references('id')->on('business_data')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_bots', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['business_data_id']);
            $table->dropColumn(['user_id', 'business_data_id']);
        });
    }
};
