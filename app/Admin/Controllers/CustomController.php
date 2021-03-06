<?php

namespace App\Admin\Controllers;


use App\Models\Boards;
use App\Models\Custom;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class CustomController extends Controller
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

            $content->header('Scheduled Pins');
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

            $content->header('Custom');
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

            $content->header('Custom');
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
        return Admin::grid(Custom::class, function (Grid $grid) {
            $grid->model()->where('status', '=', '0');
            $grid->model()->orderBy('releases_time', 'asc');

            $grid->id('ID')->sortable();
            $grid->title();
            $grid->image()->image()->display(function ($image) {
                return $image;
            });
            $grid->board()->sortable()->display(function ($board) {
                return '<div style="width: 110px">' . $board . '</div>';
            });
            $grid->note();
            $grid->link()->display(function ($link) {
                return '<a href="'.$link.'" target="_blank">' . $link . '</a>';
            });
            $grid->releases_time()->sortable()->display(function ($time) {
                return '<div style="width: 125px">' . $time . '</div>';
            });

            $grid->disableExport();

            $grid->filter(function ($filter) {
                $filter->useModal();
                // 禁用id查询框
                $filter->disableIdFilter();
                $filter->like('title', 'Title');
                $filter->is('board', 'Board')->select(Boards::all()->pluck('name', 'name'));
                $filter->like('note', 'Note');
                $filter->between('releases_time', 'Release Time')->datetime();
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
        return Admin::form(Custom::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('title', 'Title')->rules('required');
            $form->image('image')->rules('required');
            $form->select('board', 'Board')->options(Boards::all()->pluck('name', 'name'));
            $form->text('note', 'Note')->rules('required');
            $form->text('link', 'Link(非必须)')->rules('url');
            $form->datetime('releases_time', 'Release Time')->rules('required');

            $form->tools(function (Form\Tools $tools) {
                // 去掉跳转列表按钮
                $tools->disableListButton();
            });
        });
    }
}
