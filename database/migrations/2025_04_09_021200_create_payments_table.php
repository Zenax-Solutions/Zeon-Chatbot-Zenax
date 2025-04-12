<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('stripe_invoice_id')->index();
            $table->integer('amount');
            $table->string('currency', 10);
            $table->string('status');
            $table->string('invoice_url')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
