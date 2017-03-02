<?php

namespace App\Admin\Controllers;

use App\Models\HostFile;
use App\Models\Host;
use App\Models\File;

use Illuminate\Http\Request;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class HostFileController extends Controller
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
     * [postHostFileApplication description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function postHostFileApplication(Request $request)
    {
        $host_ids = $request->input('host_ids');
        $file_ids = $request->input('file_ids');
        $upload_path = $request->input('upload_path');

        array_pop($host_ids);
        array_pop($file_ids);

        $rst = [];

        foreach ($host_ids as $host_id) {
            foreach ($file_ids as $file_id) {
                $rst[] = HostFile::firstOrCreate(['host_id' => (int)$host_id, 'file_id' => (int)$file_id, 'upload_path' => $upload_path]);
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
        $host_file = HostFile::find($id);

        $host = Host::find($host_file->host_id);
        $file = File::find($host_file->file_id);

        $xml_data = array(
                        'module' => 'file_manage',
                        'func' => 'upload',
                        'info' => array(
                            'file_name' => $file->name,
                            'soft_path' => config('filesystems.disks.admin.root') . '/' . $file->path,
                            'upload_path' => $host_file->upload_path,
                            'platform_name' => $host->host_name,
                            'platform_sn' => $host->host_sn,
                            'platform_ip' => $host->host_ip,
                        )
                    );

        $socketClient = new \App\SocketClient($host->host_ip, config('app.socket_local_port'), $xml_data);
        $socket_response = $socketClient->send();
        $socketClient->close();

        if($socket_response) {
            $host_file->status = 1;
            $host_file->save();
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
        return Admin::grid(HostFile::class, function (Grid $grid) {

            // $grid->id('ID')->sortable();

            $grid->host_id('主机信息')->display(function ($host_id) {
                $host = Host::find($host_id);
                return $host->host_name . '/' . $host->host_ip . '/' . $host->host_sn;
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
        return Admin::form(HostFile::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->multipleSelect('host_ids', '主机')->options(Host::all()->pluck('host_name', 'id'))->attribute(['required'=>'required']);
            $form->multipleSelect('file_ids', '软件')->options(File::all()->pluck('name', 'id'))->attribute(['required'=>'required']);
            $form->text('upload_path', '上传路径');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');

            $form->setAction('/admin/host-file-application');
        });
    }
}
