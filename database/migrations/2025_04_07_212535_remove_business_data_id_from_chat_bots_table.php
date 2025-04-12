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
            $table->dropForeign(['business_data_id']);
            $table->dropColumn('business_data_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_bots', function (Blueprint $table) {
            $table->unsignedBigInteger('business_data_id')->nullable();
            $table->foreign('business_data_id')->references('id')->on('business_data')->onDelete('cascade');
        });
    }
};
