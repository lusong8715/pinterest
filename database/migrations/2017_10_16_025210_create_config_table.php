<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    /**
     * `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(64) NOT NULL,
    `access_token` VARCHAR(64) NOT NULL,
    `note_temp` VARCHAR(64) NOT NULL,
    `releases_num` INT(11) UNSIGNED NOT NULL,
    `releases_time` VARCHAR(64) NOT NULL,
     */
    public function up()
    {
        Schema::create('config', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 64);
            $table->string('access_token', 64);
            $table->string('note_temp', 128);
            $table->integer('releases_num')->unsigned();
            $table->string('releases_time', 64);
        });
        $config = new \App\Models\Config();
        $config->username = 'lusong8715';
        $config->access_token = 'ASdF_AHw6EdR188MCOXgGHFBhk5mFOjP29Hd0udEWzv9AYBClgAAAAA';
        $config->note_temp = '#Jeulia %title%. Discover more stunning %category% from Jeulia.com. Shop Now!';
        $config->releases_num = 20;
        $config->releases_time = '4,14,20,23';
        $config->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('config');
    }
}
