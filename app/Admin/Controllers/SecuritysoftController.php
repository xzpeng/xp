<?php

namespace App\Admin\Controllers;

use App\Models\Securitysoft;
use App\Models\Platform;
use App\Models\PlatformSoftware;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class SecuritysoftController extends Controller
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

            $content->header('安装包管理');

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

            $content->header('编辑安装包');
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

            $content->header('添加安装包');
            $content->description('新建');

            $content->body($this->form());

            Admin::script($this->script());
        });
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Securitysoft::class, function (Grid $grid) {

            //$grid->id('ID')->sortable();
            $grid->soft_name('软件名');
            /*$grid->column('soft_dir', '路径')->value(function($path){
                if($path)
                    return '<a href="' . config('admin.upload.host') . $path . '" target="_blank">下载</a>';
                else
                    return '';
            });*/
            $grid->soft_release('版本');

            $grid->created_at('添加时间');
            
            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
                $action_btn = '<a class="btn btn-primary btn-xs" href="' . url('/admin/securitysoft-packages/' . $actions->row->id) . '/edit">修改</a>';
                $actions->append($action_btn);
            });

            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
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
        return Admin::form(Securitysoft::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('soft_name', '软件名');
            $form->file('soft_dir', '选择软件');
            $form->text('soft_release', '版本');

            // $form->display('created_at', '添加时间');
            // $form->display('updated_at', '更新时间');
        });
    }

        public function script()
    {
        return <<<EOT
$('input.soft_dir').change(function(){
var soft_path = $(this).val();
var soft_paths = soft_path.split("\\\\");
var soft_name = soft_paths[soft_paths.length-1];
$('#soft_name').val(soft_name);
})
EOT;
    }
}
