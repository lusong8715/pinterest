<?php

namespace App\Providers;

use App\Models\Boards;
use App\Models\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Boards::creating(function ($board) {
            $config = Config::find(1);
            if ($config->username && $config->access_token) {
                $apiBaseUrl = 'https://api.pinterest.com/v1/boards/';
                $boardName = getBoardNameForUrl($board->name);

                $url = $apiBaseUrl . $config->username . '/' . $boardName . '/?access_token=' . $config->access_token . '&fields=id,name,url,counts';
                $result = curlRequest('get', $url);
                if (isset($result['data']) && isset($result['data']['id'])) {
                    $board->url = $result['data']['url'];
                } else {
                    $url = $apiBaseUrl . '?access_token=' . $config->access_token;
                    $data = array('name' => $board->name);
                    $row = curlRequest('post', $url, $data);
                    if (isset($row['data']) && isset($row['data']['url'])) {
                        $board->url = $row['data']['url'];
                    }
                }
            }

            return true;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
