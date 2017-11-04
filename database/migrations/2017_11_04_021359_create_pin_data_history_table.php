<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreatePinDataHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pin_data_history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('pins_id')->unsigned();
            $table->integer('saves')->unsigned()->default(0);
            $table->integer('saves_change')->default(0);
            $table->integer('comments')->unsigned()->default(0);
            $table->integer('comments_change')->default(0);
            $table->dateTime('update_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('pins_id')->references('id')->on('pins')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pin_data_history');
    }
}
