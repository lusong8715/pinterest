<?php

namespace App\Console\Commands;

use App\Models\Boards;
use App\Models\Config;
use Illuminate\Console\Command;

class updateBoards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:boards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto Update Boards';

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
        if ($config->username && $config->access_token) {
            $apiBaseUrl = 'https://api.pinterest.com/v1/boards/';
            $boards = Boards::all();
            foreach ($boards as $board) {
                $name = getBoardNameForUrl($board->name);
                $url = $apiBaseUrl . $config->username . '/' . $name . '/?access_token=' . $config->access_token . '&fields=id,name,url,counts';
                $result = curlRequest('get', $url);
                if (isset($result['data']) && isset($result['data']['id'])) {
                    $board->name = $result['data']['name'];
                    $board->url = $result['data']['url'];
                    $board->pins = $result['data']['counts']['pins'];
                    $board->collaborators = $result['data']['counts']['collaborators'];
                    $board->followers = $result['data']['counts']['followers'];
                    $board->save();
                } else {
                    if (isset($result['message']) && isset($result['type']) && $result['type'] == 'api') {
                        $board->delete();
                    }
                }
            }
        }
    }
}
