<?php

namespace App\Admin\Controllers;

use App\Models\PlatformSoftware;
use App\Models\Platform;
use App\Models\Software;

use Illuminate\Http\Request;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

use Encore\Admin\Widgets\Box;


class PlatformSoftwareController extends Controller
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
     * [postPlatformSoftwareApplication description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function postPlatformSoftwareApplication(Request $request)
    {
        $platform_ids = $request->input('platform_ids');
        $software_ids = $request->input('software_ids');

        array_pop($platform_ids);
        array_pop($software_ids);

        $rst = [];

        foreach ($platform_ids as $platform_id) {
            foreach ($software_ids as $software_id) {
                $rst[] = PlatformSoftware::firstOrCreate(['platform_id' => (int)$platform_id, 'software_id' => (int)$software_id]);
            }
        }

        return redirect('/admin/install-software');
    }


    /**
     * [softwareInstall description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function softwareInstall($id)
    {
        $platform_software = PlatformSoftware::find($id);

        $platform = Platform::find($platform_software->platform_id);
        $software = Software::find($platform_software->software_id);

        $xml_data = array(
                        'module' => 'remotefile_manage',
                        'func' => 'install_securitysoft',
                        'info' => array(
                            'securitysoft_name' => $software->name,
                            'securitysoft_dir' => config('filesystems.disks.admin.root') . '/' . $software->path,
                            'dst_platform_name' => $platform->platform_name,
                            'dst_platform_sn' => $platform->platform_sn,
                            'dst_platform_ip' => $platform->platform_ip,
                            'dst_platform_user' => 'root',
                            'dst_platform_passwd' => '123456',
                            'install_log' => config('filesystems.disks.admin.root') . '/' . $software->path.'log'
                        )
                    );

        $socketClient = new \App\SocketClient('127.0.0.1', config('app.socket_local_port'), $xml_data);
        $socket_response = $socketClient->send();
        $socketClient->close();

        if($socket_response) {
            $platform_software->status = 1;
            $platform_software->save();
        }

        return redirect()->back();
    }

    public function softwareInstalling($id)
    {
        
        return Admin::content(function (Content $content) use($id) {
            $platform_software = PlatformSoftware::find($id);

            $platform = Platform::find($platform_software->platform_id);
            $software = Software::find($platform_software->software_id);

            $content->header('安装信息');
            // $content->description('主机信息查看、管理……');
            $log_file = config('filesystems.disks.admin.root') . '/' . $software->path.'log';
            $info_html = file_get_contents($log_file);

            $actions_box = new Box('操作', $info_html);
            $content->row($actions_box);
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(PlatformSoftware::class, function (Grid $grid) {

            // $grid->id('ID')->sortable();

            $grid->platform_id('主机信息')->display(function ($platform_id) {
                $platform = Platform::find($platform_id);
                return $platform->platform_name . '/' . $platform->platform_ip . '/' . $platform->platform_sn;
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
        return Admin::form(PlatformSoftware::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->multipleSelect('platform_ids', '主机')->options(Platform::all()->pluck('platform_name', 'id'))->attribute(['required'=>'required']);
            $form->multipleSelect('software_ids', '软件')->options(Software::all()->pluck('name', 'id'))->attribute(['required'=>'required']);

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');

            $form->setAction('/admin/platform-software-application');
        });
    }
}
