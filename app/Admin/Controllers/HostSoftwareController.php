<?php

namespace App\Admin\Controllers;

use App\Models\HostSoftware;
use App\Models\Host;
use App\Models\Software;

use Illuminate\Http\Request;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class HostSoftwareController extends Controller
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

            $content->header('软件申请');
            $content->description('请选择主机和软件');

            $content->body($this->form());
        });
    }

    /**
     * [postHostSoftwareApplication description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function postHostSoftwareApplication(Request $request)
    {
        $host_ids = $request->input('host_ids');
        $software_ids = $request->input('software_ids');

        array_pop($host_ids);
        array_pop($software_ids);

        $rst = [];

        foreach ($host_ids as $host_id) {
            foreach ($software_ids as $software_id) {
                $rst[] = HostSoftware::firstOrCreate(['host_id' => (int)$host_id, 'software_id' => (int)$software_id]);
            }
        }

        return redirect('/admin/install-software');
    }


    public function softwareInstall($id)
    {
        $host_software = HostSoftware::find($id);

        $host = Host::find($host_software->host_id);
        $software = Software::find($host_software->software_id);

        $xml_data = array(
                        'module' => 'soft_manage',
                        'func' => 'install',
                        'info' => array(
                            'soft_name' => $software->username,
                            'soft_path' => config('filesystems.disks.public.root') . '/' . $software->path,
                            'platform_name' => $host->host_name,
                            'platform_sn' => $host->host_sn,
                            'platform_ip' => $host->host_ip,
                        )
                    );

        $socketClient = new \App\SocketClient($host->host_ip, config('app.socket_local_port'), $xml_data);
        $socket_response = $socketClient->send();
        $socketClient->close();

        if($socket_response) {
            $host_software->status = 1;
            $host_software->save();
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
        return Admin::grid(HostSoftware::class, function (Grid $grid) {

            // $grid->id('ID')->sortable();

            $grid->host_id('主机信息')->display(function ($host_id) {
                $host = Host::find($host_id);
                return $host->host_name . '/' . $host->host_ip . '/' . $host->host_sn;
            });
            $grid->software_id('软件')->display(function ($software_id) {
                return Software::find($software_id)->name;
            });
            $grid->column('状态')->display(function(){
                if($this->status == 0) {
                    return '已申请';
                } else if($this->status == 1) {
                    return '安装中...';
                } else if($this->status ==2) {
                    return '已安装';
                }
            });

            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();

                if ($actions->row->status == 0) {
                    $action_btn = '<a class="btn btn-primary btn-xs" href="' . url('/admin/software-install/' . $actions->row->id) . '">安装</a>';
                } else if($actions->row->status == 1) {
                    $action_btn = '<a class="btn btn-primary btn-xs" href="' . url('/admin/software-installing/' . $actions->row->id) . '">安装中...</a>';
                } else if($actions->row->status == 2) {
                    $action_btn = '<a class="btn btn-danger btn-xs" href="' . url('/admin/software-uninstall/' . $actions->row->id) . '">卸载</a>';
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
        return Admin::form(HostSoftware::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->multipleSelect('host_ids', '主机')->options(Host::all()->pluck('host_name', 'id'))->attribute(['required'=>'required']);
            $form->multipleSelect('software_ids', '软件')->options(Software::all()->pluck('name', 'id'))->attribute(['required'=>'required']);

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');

            $form->setAction('/admin/host-software-application');
        });
    }
}
