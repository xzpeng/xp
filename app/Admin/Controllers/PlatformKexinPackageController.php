<?php

namespace App\Admin\Controllers;

use App\Models\PlatformKexinPackage;
use App\Models\KexinPackage;
use App\Models\Platform;

use Illuminate\Http\Request;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class PlatformKexinPackageController extends Controller
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
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(PlatformKexinPackage::class, function (Grid $grid) {

            $grid->id('ID')->sortable();

            $grid->created_at();
            $grid->updated_at();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id)
    {
        return Admin::form(PlatformKexinPackage::class, function (Form $form) use($id) {

            $form->display('id', 'ID');
            $form->hidden('package_id')->value($id);
            $form->select('platform_id', '主机')->options(Platform::all()->pluck('platform_name', 'id'))->attribute(['required'=>'required']);

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');

            $form->setAction('/admin/distributie-kexin-package');
        });
    }


    public function distribution($id)
    {
        return Admin::content(function (Content $content) use($id) {

            $content->header('header');
            $content->description('description');

            $content->body($this->form($id));
        });
    }

    public function postDistribution(Request $request)
    {/*
        $package = KexinPackage::find($request->input('package_id'));
        $platform = Platform::find($request->input('platform_id'));

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

        return redirect()->back();*/
    }
}
