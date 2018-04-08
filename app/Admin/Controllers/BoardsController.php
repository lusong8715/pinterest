<?php

namespace App\Admin\Controllers;


use App\Models\Boards;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\Artisan;

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
            $grid->perPages([50, 100, 200]);
            $grid->paginate(50);
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

            $form->tools(function (Form\Tools $tools) {
                // 去掉跳转列表按钮
                $tools->disableListButton();
            });
        });
    }

    public function sync() {
        Artisan::call('update:boards');
        return redirect('/admin/boards');
    }
}
