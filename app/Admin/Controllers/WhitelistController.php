<?php

namespace App\Admin\Controllers;

use App\Models\Whitelist;
use App\Models\Platform;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class WhitelistController extends Controller
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

            $content->header('系统白名单');

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

            $content->header('系统白名单');
            $content->description('修改');

            $content->body($this->form()->edit($id));
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

            $content->header('系统白名单');
            $content->description('添加');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Whitelist::class, function (Grid $grid) {

            $grid->id('ID')->sortable();

            $grid->column('platform_id', '主机')->display(function ($platform_id) {
                return Platform::find($platform_id)->platform_name;
            });
            // $grid->platform_sn('序列号');
            $grid->content('白名单');

            $grid->created_at('添加时间');

            $grid->disableActions();

            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });
            $grid->disableCreation();
            $grid->disableFilter();
            $grid->disableExport();
        });
    }


    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Whitelist::class, function (Form $form) {

            $form->display('id', 'ID');

            $form->text('whitelist_name', '文件名');
            $form->file('path', '选择可信程序');
            // $form->select('platform_id', '主机')->options(Platform::all()->pluck('platform_name', 'id'))->attribute(['required'=>'required']);

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }
}
