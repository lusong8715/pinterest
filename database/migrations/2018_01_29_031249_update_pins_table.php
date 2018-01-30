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
            $table->string('image_url')->nullable();
            $table->string('original_link', 1024)->nullable();
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
            $table->dropColumn('image_url');
            $table->dropColumn('original_link');
        });
    }
}
