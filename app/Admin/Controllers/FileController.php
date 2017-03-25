<?php

namespace App\Admin\Controllers;

use App\Models\File;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

use Encore\Admin\Widgets\Box;

class FileController extends Controller
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

            $content->header('header');
            $content->description('description');

            $content->body($this->grid());
        });
    }

    public function show($id)
    {
        return Admin::content(function (Content $content) use($id) {
            $platform = File::find($id);

            $content->header('文件信息');

            $info_html = 'aaa';

            $actions_box = new Box('文件信息', $info_html);
            $content->row($actions_box);
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

            $content->header('header');
            $content->description('description');

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

            $content->header('可信程序');
            $content->description('上传可信程序');

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
        return Admin::grid(File::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->file_name('可信程序名称');
            /*$grid->column('file_dir', '路径')->value(function($path){
                if($path)
                    return '<a href="' . config('admin.upload.host') . $path . '" target="_blank">下载</a>';
                else
                    return '';
            });*/

            $grid->created_at('添加时间');
            // $grid->updated_at();

            $grid->actions(function ($actions) {
                $id = $actions->getKey();
                $actions->disableEdit();
                $actions->disableDelete();
                $actions->append('<a href="trustworthy-software/' . $id . '">查看</a>');
                $actions->append('&nbsp;|&nbsp;<a href="platform-process/' . $id . '">下发</a>');
                $actions->append('&nbsp;|&nbsp;<a href="platform-files/' . $id . '">下发记录</a>');
            });


            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });
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
        return Admin::form(File::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('file_name', '可信程序名称');
            $form->file('file_dir', '选择程序');
            // $form->checkbox('file_op', '操作')->options(['write'=>'write', 'read'=>'read', 'rename'=>'rename', 'new'=>'new', 'remove'=>'remove']);

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }
}
