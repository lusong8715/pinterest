<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pins', function (Blueprint $table) {
            $table->string('advertised', 1)->nullable()->default('0');
            $table->string('root_pin', 64)->nullable();
            $table->dateTime('repin_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pins', function (Blueprint $table) {
            $table->dropColumn('advertised');
            $table->dropColumn('root_pin');
            $table->dropColumn('repin_time');
        });
    }
}
