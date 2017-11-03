<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product', function (Blueprint $table) {
            $table->integer('id')->unsigned();
            $table->string('title', 255);
            $table->string('sku', 128);
            $table->string('url', 255);
            $table->string('category', 128);
            $table->string('sub_category', 128);
            $table->string('status', 1)->default('1');
            $table->string('is_released', 1)->nullable()->default('0');
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('product');
    }
}
