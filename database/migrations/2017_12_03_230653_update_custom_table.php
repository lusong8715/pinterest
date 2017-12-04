<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCustomTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('custom', function (Blueprint $table) {
            $table->string('advertised', 1)->nullable()->default('0');
            $table->string('root_pin')->nullable();
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
        Schema::table('custom', function (Blueprint $table) {
            $table->dropColumn('advertised');
            $table->dropColumn('root_pin');
            $table->dropColumn('repin_time');
        });
    }
}
