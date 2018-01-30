<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('config', function (Blueprint $table) {
            $table->integer('sync_board_id')->nullable();
            $table->string('sync_next_page')->nullable();
            $table->string('sync_last_date', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('config', function (Blueprint $table) {
            $table->dropColumn('sync_board_id');
            $table->dropColumn('sync_next_page');
            $table->dropColumn('sync_last_date');
        });
    }
}
