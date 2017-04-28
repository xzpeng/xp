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

    public function records($id)
    {
        return Admin::content(function (Content $content) use($id) {

            $content->header('可信程序');
            $content->description('下发记录');

            $content->body($this->gridRecords($id));
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

            $content->header('可信程序');
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

            $content->header('可信程序');
            $content->description('下发');

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

    protected function gridRecords($id)
    {
        return Admin::grid(PlatformKexinPackage::class, function (Grid $grid) use($id) {

            $grid->id('ID')->sortable();

            $grid->platform_id('主机')->display(function($platform_id){
                return Platform::find($platform_id)->platform_name;
            });
            $grid->content('可信程序');

            $grid->created_at('下发时间');

            $grid->model()->where('package_id', '=', $id);

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
    protected function form($id)
    {
        return Admin::form(PlatformKexinPackage::class, function (Form $form) use($id) {

            $form->display('id', 'ID');
            $form->hidden('package_id')->value($id);
            $form->select('platform_id', '主机')->options(Platform::all()->pluck('platform_name', 'id'))->attribute(['required'=>'required']);
            $form->text('dst_file_dir', '上传路径')->default('/home');
            $form->radio('type', '下发文件')->options(['all' => '全部', 'whitelist'=> '白名单'])->default('all');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');

            $form->setAction('/admin/distributie-kexin-package');
        });
    }


    public function distribution($id)
    {
        return Admin::content(function (Content $content) use($id) {

            $content->header('可信程序');
            $content->description('description');

            $content->body($this->form($id));
        });
    }

    public function postDistribution(Request $request)
    {
        $package_id = $request->input('package_id');
        $platform_id = $request->input('platform_id');
        $dst_file_dir = $request->input('dst_file_dir');
        $type = $request->input('type');

        $package = KexinPackage::find($package_id);
        $platform = Platform::find($platform_id);

        $func = $type=='all'?'packagetransfer':'packagetransfer-whitelist';

        $xml_data = array(
                        'module' => 'remotefile_manage',
                        'func' => $func,
                        'info' => array(
                            'package_id' => $package_id,
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
                $record->operate_result = 'success';
            } else {
                $record->operate_result = 'failed';
            }
        }

        $record->save();

        return redirect()->back();
    }
}
