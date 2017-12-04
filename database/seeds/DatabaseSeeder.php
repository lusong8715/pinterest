<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        DB::table('config')->insert([
            'username' => '',
            'access_token' => '',
            'note_temp' => '#Jeulia %title%. Discover more stunning %category% from Jeulia.com. Shop Now!',
            'releases_num' => '20',
            'releases_time' => '4,14,20,23',
        ]);

        DB::table('admin_menu')->insert(
            array(
                array(
                    'parent_id' => 0,
                    'order' => 7,
                    'title' => 'Config',
                    'icon' => 'fa-bars',
                    'uri' => 'config'
                ),
                array(
                    'parent_id' => 0,
                    'order' => 8,
                    'title' => 'Pins',
                    'icon' => 'fa-barcode',
                    'uri' => 'pins'
                ),
                array(
                    'parent_id' => 0,
                    'order' => 9,
                    'title' => 'Scheduled Pins',
                    'icon' => 'fa-folder-o',
                    'uri' => 'custom'
                ),
                array(
                    'parent_id' => 0,
                    'order' => 10,
                    'title' => 'Published Pins',
                    'icon' => 'fa-folder-open-o',
                    'uri' => 'published'
                ),
                array(
                    'parent_id' => 0,
                    'order' => 11,
                    'title' => 'Boards',
                    'icon' => 'fa-bold',
                    'uri' => 'boards'
                ),
                array(
                    'parent_id' => 0,
                    'order' => 12,
                    'title' => 'Top100',
                    'icon' => 'fa-arrow-up',
                    'uri' => 'top/saves'
                ),
            )
        );
    }
}
