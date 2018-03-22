<?php

namespace App\Console\Commands;

use App\Models\Config;
use App\Models\Pins;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class repinTop10 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repin:top10';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto Repin Top10 Pins';

    const API_BASE_URL = 'https://api.pinterest.com/v1/';
    const TOP_PIN_BOARD = 'Top Pins: Jeulia.com';

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
        $date10 = date('Y-m-d', strtotime('-10 day'));

        // 每周repin一次top10,已经repin过的隔一周再repin
        $rows = DB::select("select p.pin_id as pinid, c.title, c.image, c.note, c.link, sum(pdh.saves_change) as allsaves 
                      from custom as c 
                      inner join pins as p on p.pin_id = c.pin_id 
                      inner join pin_data_history as pdh on p.id = pdh.pins_id 
                      where c.status = '1' 
                      and pdh.update_date > ? 
                      and not exists(select 1 from pins as pi where pi.root_pin = c.pin_id and pi.created_at > ?) 
                      group by p.id order by allsaves desc, p.id desc limit 10;", [$date7, $date10]);

        foreach ($rows as $row) {
            $image = public_path('upload') . '/' . $row->image;
            $board = getBoardNameForUrl(self::TOP_PIN_BOARD);
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
                $pins->board = self::TOP_PIN_BOARD;
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
