<?php

namespace App\Console\Commands;

use App\Models\Config;
use App\Models\Pins;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class repinPins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repin:pins';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto Repin Pins';

    const API_BASE_URL = 'https://api.pinterest.com/v1/';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $config = Config::find(1);
        if (!$config->username || !$config->access_token) {
            return;
        }
        $username = $config->username;
        $accessToken = $config->access_token;
        $updateSitemap = false;
        $date7 = date('Y-m-d', strtotime('-7 day'));
        $date30 = date('Y-m-d', strtotime('-30 day'));

        // 原生pin过去30天内saves增长大于3并且发布时间超过一周时,repin
        $rows = DB::select("select pins.pin_id as pinid, custom.title, custom.image, custom.board, custom.note, custom.link 
                    from custom inner join pins on pins.pin_id = custom.pin_id 
                    where custom.status = '1'
                    and pins.advertised = '0'
                    and pins.created_at < ?
                    and not exists(select 1 from pins as p where p.root_pin = custom.pin_id) 
                    and (select sum(saves_change) as sumsave from pin_data_history as pdh where pdh.pins_id = pins.id and pdh.update_date > ? having sumsave > ?)", [$date7, $date30, 3]);

        // repin过的pin过去7天内saves增长大于10并且发布时间超过一周时,再次repin
        $rows2 = DB::select("select p.root_pin as pinid, p.title, p.board, c.image, c.note, c.link, max(p.created_at) as rdate 
                      from pins as p inner join custom c on p.root_pin = c.pin_id 
                      where p.way = '1' 
                      and p.advertised = '0' 
                      and (select sum(saves_change) as sumsave from pin_data_history as pdh where pdh.pins_id = p.id and pdh.update_date > ? having sumsave > ?) 
                      group by p.root_pin 
                      having rdate < ?", [$date7, 10, $date7]);

        $result = $rows + $rows2;
        foreach ($result as $row) {
            $image = public_path('upload') . '/' . $row->image;
            $board = strtolower(preg_replace('/\s+/', '-', $row->board));
            $data = array();
            $data['board'] = $username . '/' . $board;
            $obj = new \CurlFile($image);
            $data['image'] = $obj;
            if ($row->link) {
                $data['link'] = $row->link;
            }
            $data['note'] = $row->note;
            $url = self::API_BASE_URL . 'pins/?access_token=' . $accessToken;
            $result = curlRequest('post', $url, $data);
            if (isset($result['data']) && isset($result['data']['id'])) {
                $pins = new Pins();
                $pins->pin_id = $result['data']['id'];
                $pins->title = $row->title;
                $pins->board = $row->board;
                $pins->url = $result['data']['url'];
                $pins->way = '1';
                $pins->root_pin = $row->pinid;
                $pins->save();
                $updateSitemap = true;
            }
        }

        if ($updateSitemap) {
            updateSitemap();
        }
    }
}
