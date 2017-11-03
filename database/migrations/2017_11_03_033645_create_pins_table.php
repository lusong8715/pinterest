<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreatePinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pin_id', 64);
            $table->integer('product_id')->unsigned()->nullable();
            $table->string('product_status', 1)->nullable();
            $table->string('product_sku', 128)->nullable();
            $table->string('title', 255);
            $table->string('board', 255);
            $table->string('url', 255);
            $table->integer('saves')->unsigned()->nullable()->default(0);
            $table->integer('comments')->unsigned()->nullable()->default(0);
            $table->string('way', 1)->nullable()->default('0');
            $table->dateTime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pins');
    }
}
