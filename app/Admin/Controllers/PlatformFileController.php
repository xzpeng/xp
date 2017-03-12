<?php

namespace App\Admin\Controllers;

use App\Models\PlatformFile;
use App\Models\Platform;
use App\Models\File;

use Illuminate\Http\Request;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class PlatformFileController extends Controller
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

            $content->header('header');
            $content->description('description');

            $content->body($this->form());
        });
    }

    /**
     * [postPlatformFileApplication description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function postPlatformFileApplication(Request $request)
    {
        $platform_ids = $request->input('platform_ids');
        $file_ids = $request->input('file_ids');
        $upload_path = $request->input('upload_path');

        array_pop($platform_ids);
        array_pop($file_ids);

        $rst = [];

        foreach ($platform_ids as $platform_id) {
            foreach ($file_ids as $file_id) {
                $rst[] = PlatformFile::firstOrCreate(['platform_id' => (int)$platform_id, 'file_id' => (int)$file_id, 'upload_path' => $upload_path]);
            }
        }

        return redirect('/admin/upload-file');
    }

    /**
     * [softwareInstall description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function fileUpload($id)
    {
        $platform_file = PlatformFile::find($id);

        $platform = Platform::find($platform_file->platform_id);
        $file = File::find($platform_file->file_id);

        $xml_data = array(
                        'module' => 'file_manage',
                        'func' => 'upload',
                        'info' => array(
                            'file_name' => $file->name,
                            'soft_path' => config('filesystems.disks.admin.root') . '/' . $file->path,
                            'upload_path' => $platform_file->upload_path,
                            'platform_name' => $platform->platform_name,
                            'platform_sn' => $platform->platform_sn,
                            'platform_ip' => $platform->platform_ip,
                        )
                    );

        $socketClient = new \App\SocketClient($platform->platform_ip, config('app.socket_local_port'), $xml_data);
        $socketClient->sendHeader();
        $socket_response = $socketClient->send();
        $socketClient->close();

        if($socket_response) {
            $platform_file->status = 1;
            $platform_file->save();
        }

        return redirect()->back();
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(PlatformFile::class, function (Grid $grid) {

            // $grid->id('ID')->sortable();

            $grid->platform_id('主机信息')->display(function ($platform_id) {
                $platform = Platform::find($platform_id);
                return $platform->platform_name . '/' . $platform->platform_ip . '/' . $platform->platform_sn;
            });
            $grid->file_id('文件')->display(function ($file_id) {
                return File::find($file_id)->name;
            });
            $grid->upload_path('上传路径');
            $grid->column('状态')->display(function(){
                if($this->status == 0) {
                    return '已申请';
                } else if($this->status == 1) {
                    return '上传中...';
                } else if($this->status ==2) {
                    return '已上传';
                }
            });

            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();

                if ($actions->row->status == 0) {
                    $action_btn = '<a class="btn btn-primary btn-xs" href="' . url('/admin/file-upload/' . $actions->row->id) . '">上传</a>';
                } else if($actions->row->status == 1) {
                    $action_btn = '<a class="btn btn-primary btn-xs" href="' . url('/admin/file-uploading/' . $actions->row->id) . '">上传中...</a>';
                } else if($actions->row->status == 2) {
                    $action_btn = '<a class="btn btn-danger btn-xs" href="' . url('/admin/file-remove/' . $actions->row->id) . '">移除</a>';
                } else {
                    $action_btn = '';
                }

                $actions->append($action_btn);
            });

            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });

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
        return Admin::form(PlatformFile::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->multipleSelect('platform_ids', '主机')->options(Platform::all()->pluck('platform_name', 'id'))->attribute(['required'=>'required']);
            $form->multipleSelect('file_ids', '软件')->options(File::all()->pluck('name', 'id'))->attribute(['required'=>'required']);
            $form->text('upload_path', '上传路径');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');

            $form->setAction('/admin/platform-file-application');
        });
    }
}
