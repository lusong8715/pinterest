<?php

namespace App\Admin\Controllers;


use App\Models\Boards;
use App\Models\Config;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class BoardsController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Boards');
            $content->description('list');

            $content->body($this->grid());
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('Boards');
            $content->description('create');
            $content->body($this->form());
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

            $content->header('Boards');
            $content->description('edit');
            $content->body($this->form()->edit($id));
        });
    }

    public function store()
    {
        $config = Config::find(1);
        if ($config->username && $config->access_token) {
            $apiBaseUrl = 'https://api.pinterest.com/v1/boards/';
            $board = strtolower(preg_replace('/\s+/', '-', $_POST['name']));

            $url = $apiBaseUrl . $config->username . '/' . $board . '/?access_token=' . $config->access_token . '&fields=id,name,url,counts';
            $result = curlRequest('get', $url);
            if (!isset($result['data']) || !isset($result['data']['id'])) {
                $url = $apiBaseUrl . '?access_token=' . $config->access_token;
                $data = array('name' => $_POST['name']);
                curlRequest('post', $url, $data);
            }
        }

        return $this->form()->store();
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Boards::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->name()->display(function ($name) {
                return '<a href="'.$this->url.'" target="_blank">' . $name . '</a>';
            });
            $grid->pins()->sortable();
            $grid->collaborators()->sortable();
            $grid->followers()->sortable();
            $hours = array();
            for ($i=0; $i<24; $i++) {
                $hours[] = $i;
            }
            $grid->sync_pin_time()->editable('select', $hours);

            $grid->disableExport();
            $grid->disableRowSelector();
            $grid->actions(function ($actions) {
                $actions->disableEdit();
            });
            $grid->tools(function ($tools) {
                $tools->disableRefreshButton();
                $elem = '<a class="btn btn-sm btn-primary" href="boards/sync"><i class="fa fa-refresh"></i> 同步</a>';
                $tools->append($elem);
            });

            $grid->filter(function ($filter) {
                // 禁用id查询框
                $filter->disableIdFilter();
                $filter->like('name', 'Name');
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Boards::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('name', 'Name')->rules('required');
            $hours = array();
            for ($i=0; $i<24; $i++) {
                $hours[] = $i;
            }
            $form->select('sync_pin_time', 'Sync Pin Time')->options($hours)->default(2);

            $form->tools(function (Form\Tools $tools) {
                // 去掉跳转列表按钮
                $tools->disableListButton();
            });
        });
    }

    public function sync() {
        $config = Config::find(1);
        if ($config->username && $config->access_token) {
            $apiBaseUrl = 'https://api.pinterest.com/v1/boards/';
            $boards = Boards::all();
            foreach ($boards as $board) {
                $name = strtolower(preg_replace('/\s+/', '-', $board->name));
                $url = $apiBaseUrl . $config->username . '/' . $name . '/?access_token=' . $config->access_token . '&fields=id,name,url,counts';
                $result = curlRequest('get', $url);
                if (isset($result['data']) && isset($result['data']['id'])) {
                    if ($name == strtolower(preg_replace('/\s+/', '-', $result['data']['name']))) {
                        $board->name = $result['data']['name'];
                    }
                    $board->url = $result['data']['url'];
                    $board->pins = $result['data']['counts']['pins'];
                    $board->collaborators = $result['data']['counts']['collaborators'];
                    $board->followers = $result['data']['counts']['followers'];
                    $board->save();
                }
            }
        }
        return redirect('/admin/boards');
    }
}
