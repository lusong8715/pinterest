<?php

namespace App\Console\Commands;

use App\Models\Boards;
use App\Models\Config;
use App\Models\Custom;
use App\Models\Pins;
use App\Models\Product;
use Illuminate\Console\Command;

class releasePins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'release:pins';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto Release Pins';

    const API_BASE_URL = 'https://api.pinterest.com/v1/';
    const IMAGE_BASE_URL = 'https://res.jeulia.com/media/catalog/product/merge/';

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
        date_default_timezone_set('America/New_York');
        // ---------------Start Release Custom-----------------
        $nowTime = date('Y-m-d H');
        $customs = Custom::where('status', '=', '0')->where('releases_time', '>=', $nowTime)->get();
        if ($customs) {
            foreach ($customs as $custom) {
                $releasesTime = substr($custom->releases_time, 0, strpos($custom->releases_time, ':'));
                if ($nowTime == $releasesTime) {
                    $image = public_path('upload') . '/' . $custom->image;
                    $board = strtolower(preg_replace('/\s+/', '-', $custom->board));

                    $url = self::API_BASE_URL . 'boards/' . $username . '/' . $board . '/?access_token=' . $accessToken;
                    $result = curlRequest('get', $url);
                    if (!isset($result['data']) || !isset($result['data']['id'])) {
                        $url = self::API_BASE_URL . 'boards/?access_token=' . $accessToken;
                        $data = array('name' => $custom->board);
                        curlRequest('post', $url, $data);
                    }

                    $data = array();
                    $data['board'] = $username . '/' . $board;
                    $obj = new \CurlFile($image);
                    $data['image'] = $obj;
                    if ($custom->link) {
                        $data['link'] = $custom->link;
                    }
                    $data['note'] = $custom->note;
                    $url = self::API_BASE_URL . 'pins/?access_token=' . $accessToken;
                    $result = curlRequest('post', $url, $data);
                    if (isset($result['data']) && isset($result['data']['id'])) {
                        $custom->status = '1';
                        $custom->url = $result['data']['url'];
                        $custom->save();
                        $pins = new Pins();
                        $pins->pin_id = $result['data']['id'];
                        $pins->title = $custom->title;
                        $pins->board = $custom->board;
                        $pins->url = $result['data']['url'];
                        $pins->way = '1';
                        $pins->save();
                        $updateSitemap = true;
                    }
                }
            }
        }
        // ---------------End Release Custom-----------------

        // ---------------Start Release Product-----------------
        $runTime = explode(',', $config->releases_time);
        $nowH = date('H');
        if (!in_array($nowH, $runTime)) {
            return;
        }
        $products = new Product();
        $products = $products->where('is_released', '=', '0')->take($config->releases_num)->get();
        foreach ($products as $product) {
            $imageUrl = self::IMAGE_BASE_URL . rawurlencode($product->sku) . '.jpg';
            $category = $product->category;
            $board = strtolower(preg_replace('/\s+/', '-', $category));

            $url = self::API_BASE_URL . 'boards/' . $username . '/' . $board . '/?access_token=' . $accessToken;
            $result = curlRequest('get', $url);
            if (!isset($result['data']) || !isset($result['data']['id'])) {
                $url = self::API_BASE_URL . 'boards/?access_token=' . $accessToken;
                $data = array('name' => $category);
                $result = curlRequest('post', $url, $data);
                if (isset($result['data']) && isset($result['data']['id'])) {
                    $boards = new Boards();
                    $boards->name = $result['data']['name'];
                    $boards->name = $result['data']['url'];
                    $boards->save();
                }
            }

            $data = array();
            $data['board'] = $username . '/' . $board;
            $data['image_url'] = $imageUrl;
            $data['link'] = $product->url;
            $note = $config->note_temp;
            $note = str_replace('%title%', $product->title, $note);
            $note = str_replace('%category%', $product->sub_category, $note);
            $data['note'] = $note;
            $url = self::API_BASE_URL . 'pins/?access_token=' . $accessToken;
            $result = curlRequest('post', $url, $data);
            if (isset($result['data']) && isset($result['data']['id'])) {
                $pins = new Pins();
                $pins->pin_id = $result['data']['id'];
                $pins->product_id = $product->id;
                $pins->product_sku = $product->sku;
                $pins->title = $product->title;
                $pins->board = $board;
                $pins->url = $result['data']['url'];
                $pins->save();
                $product->is_released = '1';
                $product->save();
                $updateSitemap = true;
            }
        }
        // ---------------End Release Product-----------------

        if ($updateSitemap) {
            // update sitemp
            $filename = public_path('upload') . '/sitemap.xml';
            $fp = fopen($filename, 'w');
            $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
            $pins = Pins::all();
            foreach ($pins as $pin) {
                $xml .= sprintf('<sitemap><loc>%s</loc><lastmod>%s</lastmod></sitemap>', $pin->url, date('Y-m-d') );
            }
            $xml .= '</urlset>';
            fwrite($fp, $xml);
            fclose($fp);
        }
    }
}
