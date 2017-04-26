<?php

namespace App\Admin\Controllers;

use App\Models\PlatformSecuritysoft;
use App\Models\Platform;
use App\Models\Securitysoft;

use Illuminate\Http\Request;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

use Encore\Admin\Widgets\Box;


class PlatformSecuritysoftController extends Controller
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

            $content->header('安装申请');
            // $content->description('description');

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

            $content->header('安装申请');
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

            $content->header('安装申请');
            $content->description('请填写设备信息，选择申请安装的软件');

            $content->body($this->form());
        });
    }

    /**
     * [postPlatformSecuritysoftApplication description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function postPlatformSecuritysoftApplication(Request $request)
    {
        $platform_name = $request->input('platform_name');
        $platform_ip = $request->input('platform_ip');
        $platform_sn = $request->input('platform_sn');
        $alive = $request->input('alive');
        $platform_root = $request->input('platform_root');
        $platform_rootpwd = $request->input('platform_rootpwd');
        $install_status = 0;
        $securitysoft_id = $request->input('securitysoft_id');

        $platform = Platform::firstOrCreate(['platform_sn' => $platform_sn]);

        $platform->platform_name = $platform_name;
        $platform->platform_ip = $platform_ip;
        $platform->alive = $alive;
        $platform->platform_root = $platform_root;
        $platform->platform_rootpwd = $platform_rootpwd;
        $platform->install_status = $install_status;
        $platform->securitysoft_id = $securitysoft_id;

        if ( $platform->save() ) {
            return redirect('/admin/install-securitysoft');
        } else {
            return redirect()->back();
        }
    }


    /**
     * [softwareInstall description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function securitysoftInstall($id)
    {

        $platform = Platform::find($id);
        $securitysoft = Securitysoft::find($platform->securitysoft_id);

        $xml_data = array(
                        'module' => 'remotefile_manage',
                        'func' => 'install_securitysoft',
                        'info' => array(
                            'securitysoft_name' => config('filesystems.disks.admin.root') . '/' . $securitysoft->soft_dir,
                            'securitysoft_dir' => '/',
                            'dst_platform_name' => $platform->platform_name,
                            'dst_platform_sn' => $platform->platform_sn,
                            'dst_platform_ip' => $platform->platform_ip,
                            'dst_platform_user' => $platform->platform_root,
                            'dst_platform_passwd' => $platform->platform_rootpwd,
                            'install_log' => config('filesystems.disks.admin.root') . '/' . $securitysoft->soft_dir.'.log'
                        )
                    );

        file_put_contents(config('filesystems.disks.admin.root') . '/' . $securitysoft->soft_dir.'.log', '');

        $socketClient = new \App\SocketClient(config('app.socket_local_host'), config('app.socket_local_port'), $xml_data);
        $socket_response = $socketClient->send();
        $socketClient->close();

        if($socket_response) {
            $platform->install_status = 1;
            $platform->save();
        }

        return redirect()->back();
    }

    public function securitysoftInstalling($id)
    {
        
        return Admin::content(function (Content $content) use($id) {

            $platform = Platform::find($id);
            $securitysoft = Securitysoft::find($platform->securitysoft_id);

            $content->header('安装信息');
            // $content->description('主机信息查看、管理……');
            $log_file = config('filesystems.disks.admin.root') . '/' . $securitysoft->soft_dir.'.log';


            $info_html = file_get_contents($log_file);

            $info_html = str_replace("\n", '<br>', $info_html);

            $actions_box = new Box('安装日志', $info_html);
            $content->row($actions_box);

            if (strstr($info_html, 'success')) {
                $platform->install_status = 2;
                $platform->save();
            } else {
                Admin::script($this->script());
            }

        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Platform::class, function (Grid $grid) {

            // $grid->id('ID')->sortable();

            $grid->platform_name('主机名')->editable();
            $grid->platform_ip('IP地址')->editable();

            $grid->securitysoft_id('软件包名')->display(function ($securitysoft_id) {
                return Securitysoft::find($securitysoft_id)->soft_name;
            });
            $grid->platform_root('root账号')->editable();
            $grid->platform_rootpwd('root密码')->editable();

            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();

                if ($actions->row->install_status == 0) {
                    $action_btn = '<a class="btn btn-primary btn-xs" href="' . url('/admin/securitysoft-install/' . $actions->row->id) . '">安装</a>';
                } else if($actions->row->install_status == 1) {
                    $action_btn = '<a class="btn btn-primary btn-xs" href="' . url('/admin/securitysoft-installing/' . $actions->row->id) . '">安装中...</a>';
                } else if($actions->row->install_status == 2) {
                    $action_btn = '<a class="btn btn-danger btn-xs" href="' . url('/admin/securitysoft-uninstall/' . $actions->row->id) . '">卸载</a>';
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
        return Admin::form(PlatformSecuritysoft::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('platform_name', '主机名')->attribute(['required'=>'required']);
            $form->text('platform_ip', 'IP地址')->attribute(['required'=>'required']);
            // $form->text('platform_sn', '序列号')->attribute(['required'=>'required']);
            $form->hidden('platform_sn')->default(md5(time()));
            $form->text('platform_root', 'root账号')->default('root')->attribute(['required'=>'required']);
            $form->password('platform_rootpwd', 'root密码')->attribute(['required'=>'required']);
            $form->radio('alive', '状态')->options([1=> 'alive', 0 => 'Dead'])->default('1');
            //$form->multipleSelect('soft_ids', '软件')->options(Securitysoft::all()->pluck('soft_name', 'id'))->attribute(['required'=>'required']);
            $form->select('securitysoft_id', '安全软件')->options(Securitysoft::all()->pluck('soft_name', 'id'))->attribute(['required'=>'required']);

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');

            $form->setAction('/admin/platform-securitysoft-application');
        });
    }

    public function script()
    {
        return <<<EOT
setTimeout(function(){
    window.location.reload();
},3000);
EOT;
    }

}
