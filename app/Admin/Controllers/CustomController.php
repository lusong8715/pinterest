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
    protected function grid($published=false)
    {
        return Admin::grid(Custom::class, function (Grid $grid) use ($published) {
            $grid->model()->where('status', '=', $published ? '1' : '0');
            $grid->model()->orderBy('releases_time', 'desc');

            $grid->id('ID')->sortable();
            $grid->title();
            $grid->image()->image();
            $grid->board()->sortable();
            $grid->note();
            $grid->link();
            $grid->status()->display(function () use ($published) {
                return $published ? '已发布' : '未发布';
            });;
            $grid->releases_time()->sortable();

            $grid->disableExport();
            if ($published) {
                $grid->disableCreation();
                $grid->actions(function ($actions) {
                    $actions->disableEdit();
                });
            }

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

    public function published() {
        return Admin::content(function (Content $content) {
            $content->header('Published Pins');
            $content->description('list');
            $content->body($this->grid(true));
        });
    }
}
