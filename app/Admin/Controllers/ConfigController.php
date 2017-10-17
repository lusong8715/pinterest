<?php

namespace App\Admin\Controllers;

use App\Models\Config;

use App\Models\Product;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class ConfigController extends Controller
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

            $content->header('Config');
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

            $content->header('Config');
            $content->description('edit');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Config::class, function (Grid $grid) {

            $grid->id('ID');
            $grid->username();
            $grid->access_token();
            $grid->note_temp();
            $grid->releases_num();
            $grid->releases_time();
            $grid->column('surplus_num')->display(function () {
                $count = Product::where('is_released', '=', '0')->count();
                return $count;
            });

            $grid->disableCreation();
            $grid->disablePagination();
            $grid->disableFilter();
            $grid->disableExport();
            $grid->disableRowSelector();
            $grid->actions(function ($actions) {
                $actions->disableDelete();
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
        return Admin::form(Config::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('username', 'User Name')->rules('required');
            $form->text('access_token', 'Access Token')->rules('required');
            $form->text('note_temp', 'Note Template')->rules('required');
            $form->number('releases_num', 'Releases Number')->rules('required');
            $form->text('releases_time', 'Releases Time(多个用,隔开)')->rules('required');

            $form->tools(function (Form\Tools $tools) {
                // 去掉跳转列表按钮
                $tools->disableListButton();
            });
        });
    }
}
