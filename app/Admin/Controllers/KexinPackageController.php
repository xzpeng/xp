<?php

namespace App\Admin\Controllers;

use App\Models\KexinPackage;
use App\Models\KexinPackageContent;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

use Encore\Admin\Widgets\Box;

class KexinPackageController extends Controller
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
        $package_content_count = KexinPackageContent::where('package_id', $id)->count();
        if($package_content_count<=0) {

            $package = KexinPackage::find($id);

            $xml_data = array(
                            'module' => 'remotefile_manage',
                            'func' => 'packageanalysis',
                            'info' => array(
                                'file_id' => $id,
                                'file_name' => config('filesystems.disks.admin.root') . '/' . $package->package_dir
                            )
                        );

            $socketClient = new \App\SocketClient(config('app.socket_local_host'), config('app.socket_local_port'), $xml_data);
            $socket_response = $socketClient->send();
            $socketClient->close();
            
        }

        if(isset($socket_response) && !$socket_response) {
            return Admin::content(function (Content $content) use($id) {

                $content->header('文件信息');

                $content->body('解压失败');
            });
        }

        return Admin::content(function (Content $content) use($id) {

            $content->header('文件信息');

            $content->body($this->gridContent($id));
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
        return Admin::grid(KexinPackage::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->package_name('可信程序名称');
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
                $actions->append('<a href="kexin-package/' . $id . '">查看</a>');
                $actions->append('&nbsp;|&nbsp;<a href="distribution-kexin-package/' . $id . '">下发</a>');
                $actions->append('&nbsp;|&nbsp;<a href="distribution-records/' . $id . '">下发记录</a>');
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

    protected function gridContent($id)
    {
        return Admin::grid(KexinPackageContent::class, function (Grid $grid) {
            $grid->id('ID')->sortable();
            $grid->file_name('文件名');

            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });
            $grid->disableActions();
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
        return Admin::form(KexinPackage::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('package_name', '可信程序名称');
            $form->file('package_dir', '选择程序');
            
            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }
}
