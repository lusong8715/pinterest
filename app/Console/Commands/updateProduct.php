<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class updateProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:product';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Magento Products';

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
        $url = 'https://s3.amazonaws.com/static.jeulia.com/feed/pins-data.txt';
        $f = fopen($url, 'r');
        while(false !== ($data = fgetcsv($f, null, '~'))){
            if (!is_numeric($data[0])) {
                continue;
            }
            $id = $data[0];
            $title = $data[1];
            $sku = $data[2];
            $url = $data[3];
            $category = $data[4];
            $subCategory = $data[5];

            $product = Product::find($id);
            if ($product) {
                $flag = false;
                if ($product->title != $title) {
                    $product->title = $title;
                    $flag = true;
                }
                if ($product->sku != $sku) {
                    $product->sku = $sku;
                    $flag = true;
                }
                if ($product->url != $url) {
                    $product->url = $url;
                    $flag = true;
                }
                if ($product->category != $category) {
                    $product->category = $category;
                    $flag = true;
                }
                if ($product->sub_category != $subCategory) {
                    $product->sub_category = $subCategory;
                    $flag = true;
                }
                if ($flag) {
                    $product->save();
                }
            } else {
                $product = new Product();
                $product->id = $id;
                $product->title = $title;
                $product->sku = $sku;
                $product->url = $url;
                $product->category = $category;
                $product->sub_category = $subCategory;
                $product->save();
            }
        }
        fclose($f);
    }
}
