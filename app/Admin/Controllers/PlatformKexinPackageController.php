<?php

namespace App\Admin\Controllers;

use App\Models\PlatformKexinPackage;
use App\Models\KexinPackage;
use App\Models\Platform;
use App\Models\KexinPackageTransferRecord;

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
            $form->text('dst_file_dir', '上传路径')->default('/home');

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
    {
        $package_id = $request->input('package_id');
        $platform_id = $request->input('platform_id');
        $dst_file_dir = $request->input('dst_file_dir');

        $package = KexinPackage::find($package_id);
        $platform = Platform::find($platform_id);

        $xml_data = array(
                        'module' => 'remotefile_manage',
                        'func' => 'packagetransfer',
                        'info' => array(
                            'src_file_name' => config('filesystems.disks.admin.root') . '/' . $package->package_dir,
                            'dst_platform_ip' => $platform->platform_ip,
                            'dst_file_dir' => $dst_file_dir
                        )
                    );

        $socketClient = new \App\SocketClient(config('app.socket_local_host'), config('app.socket_local_port'), $xml_data);
        $socket_response = $socketClient->send();
        $socketClient->close();

        $record = new KexinPackageTransferRecord;

        $record->package_id = $package_id;
        $record->platform_id = $platform_id;
        $record->dst_file_dir = $dst_file_dir;
        //$record->operation_at = ;

        if($socket_response) {
            if (strstr('success', $socket_response)) {
                $record->operation_result = 'success';
            } else {
                $record->operation_result = 'failed';
            }
        }

        $record->save();

        return redirect()->back();
    }
}
