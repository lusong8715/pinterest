<?php

namespace App\Admin\Controllers;

use App\Models\Boards;
use App\Models\Config;
use App\Models\Pins;

use App\Models\Product;
use Encore\Admin\Grid;
use Encore\Admin\Form;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class PinsController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Pins');
            $content->description('list');

            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('Pins');
            $content->description('edit');

            $content->body($this->form()->edit($id));
        });
    }

    public function destroy($id)
    {
        $apiBaseUrl = 'https://api.pinterest.com/v1/pins/';
        $config = Config::find(1);
        if (!$config->username || !$config->access_token) {
            return;
        }
        $pinsIds = explode(',', $id);
        foreach ($pinsIds as $pinsId) {
            $pins = Pins::find($pinsId);
            $url = $apiBaseUrl . $pins->pin_id . '/?access_token=' . $config->access_token;
            $result = curlRequest('delete', $url);
            if (isset($result['status']) && $result['status'] == 'failure') {
                continue;
            }
            $pins->delete();
        }
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Pins::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->pin_id();
            $grid->product_id();
            $grid->product_status()->editable('select', [1 => 1, 0 => 0]);
            $grid->product_sku();
            $grid->title()->limit(30);
            $grid->board();
            $grid->url()->display(function ($url) {
                $text = $url;
                if (substr($url, -1) == '/') {
                    $text = substr($url, 0, -1);
                }
                $arr = explode('/', $text);
                $text = end($arr);
                return '<a href="'.$url.'" target="_blank">' . $text . '</a>';
            });
            $grid->saves()->sortable();
            $grid->comments()->sortable();
            $grid->is_custom();
            $grid->created_at()->sortable();

            $grid->filter(function ($filter) {
                $filter->useModal();
                // 禁用id查询框
                $filter->disableIdFilter();
                $filter->is('product_id', 'Product Id');
                $filter->is('product_status', 'Product Status')->select([1 => 'Enable', 0 => 'Disable']);
                $filter->is('product_sku', 'Product Sku');
                $filter->like('title', 'Title');
                $filter->is('board', 'Pin Board')->select(Boards::all()->pluck('name', 'name'));
                $filter->between('saves', 'Pin Saves');
                $filter->between('comments', 'Pin Comments');
                $filter->is('is_custom', 'Is Custom')->select([1 => 'Yes', 0 => 'No']);
                $filter->between('created_at', 'Pin Created Time')->datetime();
            });

            $grid->disableCreation();
            $grid->disableActions();
            $grid->perPages([20, 40, 60, 80, 100]);
        });
    }

    protected function form()
    {
        return Admin::form(Pins::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('product_status', 'Product Status');
            $form->display('created_at', 'Created At');
        });
    }
}
