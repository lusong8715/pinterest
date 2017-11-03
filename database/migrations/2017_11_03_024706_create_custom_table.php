<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 255);
            $table->string('image', 128);
            $table->string('board', 255);
            $table->string('note', 1024);
            $table->string('link', 1024)->nullable();
            $table->string('status', 1)->nullable()->default('0');
            $table->dateTime('releases_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('custom');
    }
}
