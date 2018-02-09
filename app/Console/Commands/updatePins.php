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
        $today = date('Y-m-d');
        if ($config->sync_last_date) {
            if (!$config->sync_board_id && !$config->sync_next_page && $config->sync_last_date == $today) {
                return;
            }
        }
        $bid = (int)$config->sync_board_id;

        $boards = Boards::where('id', '>=', $bid)->orderBy('id')->get();
        $maxId = Boards::max('id');

        foreach ($boards as $board) {
            $config->sync_board_id = $board->id;
            $next = '';
            while ($next !== false) {
                $boardName = getBoardNameForUrl($board->name);
                $url = self::API_BASE_URL . 'boards/' . $config->username . '/' . $boardName . '/pins/?access_token=' . $config->access_token . '&fields=id%2Curl%2Ccounts%2Cnote%2Cimage%2Coriginal_link';
                if ($next) {
                    $url .= '&cursor=' . $next;
                }
                $result = curlRequest('get', $url);
                if (isset($result['page']) && $result['page']['cursor']) {
                    $config->sync_next_page = $next = $result['page']['cursor'];
                } else {
                    $next = false;
                    if ($board->id == $maxId) {
                        $config->sync_board_id = 0;
                    }
                    $config->sync_next_page = '';
                }
                if (isset($result['data'])) {
                    foreach ($result['data'] as $data) {
                        $pins = Pins::where('pin_id', '=', $data['id'])->take(1)->get();
                        if (count($pins)) {
                            $pins = $pins[0];
                            $updateFlag = false;
                            if ($pins->saves != $data['counts']['saves']) {
                                $pins->saves = $data['counts']['saves'];
                                $updateFlag = true;
                            }
                            if ($pins->comments != $data['counts']['comments']) {
                                $pins->comments = $data['counts']['comments'];
                                $updateFlag = true;
                            }
                            if ($pins->image_url != $data['image']['original']['url']) {
                                $pins->image_url = $data['image']['original']['url'];
                                $updateFlag = true;
                            }
                            if ($pins->original_link != $data['original_link']) {
                                $pins->original_link = $data['original_link'];
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
                            $pins->image_url = $data['image']['original']['url'];
                            $pins->original_link = $data['original_link'];
                            $pins->way = '2';
                            $pins->save();
                        }
                        unset($pins);
                    }
                } else {
                    break 2;
                }
                unset($result);
            }
        }
        $config->sync_last_date = $today;
        $config->save();

        $deleteDate = date('Y-m-d', strtotime('-30 day'));
        PinDataHistory::where('update_date', '<', $deleteDate)->delete();
    }
}
