<?php

namespace App\Console\Commands;

use App\Models\Boards;
use App\Models\Config;
use App\Models\PinDataHistory;
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
        ini_set('memory_limit', '1024M');
        $config = Config::find(1);
        if (!$config->username || !$config->access_token) {
            return;
        }
        $nowH = date('H');
        if (strlen($nowH) == 2 && substr($nowH, 0, 1) == '0') {
            $nowH = substr($nowH, 1);
        }
        $boards = Boards::where('sync_pin_time', '=', $nowH)->get();
        foreach ($boards as $board) {
            $next = '';
            while ($next !== false) {
                $boardName = strtolower(preg_replace('/\s+/', '-', $board->name));
                $url = $next == '' ? self::API_BASE_URL . 'boards/' . $config->username . '/' . $boardName . '/pins/?access_token=' . $config->access_token . '&fields=id%2Curl%2Ccounts%2Cnote' : $next;
                $result = curlRequest('get', $url);
                if (isset($result['page']) && $result['page']['cursor']) {
                    $next = $result['page']['next'];
                } else {
                    $next = false;
                }
                if (isset($result['data'])) {
                    foreach ($result['data'] as $data) {
                        $savesChange = 0;
                        $commentsChanage = 0;
                        $pins = Pins::where('pin_id', '=', $data['id'])->take(1)->get();
                        if (count($pins)) {
                            $pins = $pins[0];
                            $updateFlag = false;
                            if ($pins->saves != $data['counts']['saves']) {
                                $savesChange = $data['counts']['saves'] - $pins->saves;
                                $pins->saves = $data['counts']['saves'];
                                $updateFlag = true;
                            }
                            if ($pins->comments != $data['counts']['comments']) {
                                $commentsChanage = $data['counts']['comments'] - $pins->comments;
                                $pins->comments = $data['counts']['comments'];
                                $updateFlag = true;
                            }
                            if ($updateFlag) {
                                $pins->save();
                            }
                        } else {
                            $pins = new Pins();
                            $pins->pin_id = $data['id'];
                            $pins->board = $board->name;
                            $pins->url = $data['url'];
                            $pins->saves = $data['counts']['saves'];
                            $pins->comments = $data['counts']['comments'];
                            $title = trim($data['note']);
                            if ($title == '') {
                                $title = 'No Title';
                            } else {
                                if (strlen($title) > 100) {
                                    $title = substr($title, 0, 100) . '...';
                                }
                            }
                            $pins->title = $title;
                            $pins->way = '2';
                            $pins->save();
                        }
                        // update pin_data_history
                        $pinDataHistory = new PinDataHistory();
                        $pinDataHistory->pins_id = $pins->id;
                        $pinDataHistory->saves = $pins->saves;
                        $pinDataHistory->saves_change = $savesChange;
                        $pinDataHistory->comments = $pins->comments;
                        $pinDataHistory->comments_change = $commentsChanage;
                        $pinDataHistory->save();
                    }
                } else {
                    $next = false;
                }
            }
        }

        $deleteDate = date('Y-m-d', strtotime('-30 day'));
        PinDataHistory::where('update_date', '<', $deleteDate)->delete();
    }
}
