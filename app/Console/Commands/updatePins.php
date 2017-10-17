<?php

namespace App\Console\Commands;

use App\Models\Config;
use App\Models\Pins;
use Illuminate\Console\Command;

class updatePins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:pins';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto Update Pins';

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
        $datas = Pins::all();
        foreach ($datas as $pins) {
            $updateFlag = false;
            $url = self::API_BASE_URL . 'pins/' . $pins->pin_id . '/?access_token=' . $config->access_token . '&fields=counts';
            $result = curlRequest('get', $url);
            if (isset($result['data']) && isset($result['data']['counts'])) {
                $counts = $result['data']['counts'];
                if (isset($counts['saves']) && $pins->saves != $counts['saves']) {
                    $pins->saves = $counts['saves'];
                    $updateFlag = true;
                }
                if (isset($counts['comments']) && $pins->comments != $counts['comments']) {
                    $pins->comments = $counts['comments'];
                    $updateFlag = true;
                }
            }
            if ($updateFlag) {
                $pins->save();
            }
        }
    }
}
