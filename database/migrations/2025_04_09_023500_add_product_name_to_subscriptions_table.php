<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductNameToSubscriptionsTable extends Migration
{
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('product_name')->nullable()->after('type');
        });
    }

    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('product_name');
        });
    }
}
