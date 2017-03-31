<?php

namespace App\Admin\Controllers;

use App\Models\Platform;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Tab;
use Encore\Admin\Widgets\Table;

class PlatformController extends Controller
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
        return Admin::content(function (Content $content) use($id) {
            $platform = Platform::find($id);

            $content->header('设备状态监控');
            // $content->description('主机信息查看、管理……');

            $tab = new Tab();

            if($platform_system_info = json_decode($platform->platform_system_info, true)) {
                $boottime = $platform_system_info['boottime'];
                $cpu_stat = isset($platform_system_info['cpu'])?$platform_system_info['cpu']:0;
                $memory_stat = ( isset($platform_system_info['memory'])&&isset($platform_system_info['memory']['available'])&&isset($platform_system_info['memory']['total']) )?round(($platform_system_info['memory']['available']*100/$platform_system_info['memory']['total']), 1):0;

                $disk_html = '';
                foreach($platform_system_info['disk'] as $key => $value) {
                    $disk_html .= '<div class="col-sm-8">'.$key.'
                                <div class="progress">
                                    <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="100" style="width: ' . $value['usage'] . '%">' . $value['usage'] . '
                                      <span class="sr-only"></span>
                                    </div>
                                </div>
                            </div>';
                }

                $system_release = isset($platform_system_info['system_release'])?$platform_system_info['system_release']:'';
            } else {
                $cpu_stat = 0;
                $memory_stat = 0;
                $system_release = '';
            }
            $cpu_stat_class = $cpu_stat<50?'progress-bar-success':($cpu_stat>75?'progress-bar-danger':'progress-bar-warning');
            $memory_stat_class = $memory_stat<50?'progress-bar-success':($memory_stat>75?'progress-bar-danger':'progress-bar-warning');

            $platform_alive = $platform->alive?'<span class="label label-info">Alive</span>':'<span class="label label-default">Dead</span>';


            $info_html = <<<HTML
<div class="col-xs-offset-2 col-xs-8">
<table class="table table-striped">
    <tr>
        <td>主机名：</td>
        <td>$platform->platform_name</td>
    </tr>
    <tr>
        <td>IP地址：</td>
        <td>$platform->platform_ip</td>
    </tr>
    <tr>
        <td>序列号：</td>
        <td>$platform->platform_sn</td>
    </tr>
    <tr>
        <td>CPU：</td>
        <td title="$cpu_stat%">
            <div class="col-sm-12">
                <div class="progress">
                    <div class="progress-bar $cpu_stat_class progress-bar-striped active" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="100" style="width: $cpu_stat%">
                        $cpu_stat%
                    </div>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td>内存：</td>
        <td title="$memory_stat%">
            <div class="col-sm-12">
                <div class="progress">
                    <div class="progress-bar $memory_stat_class progress-bar-striped active" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="100" style="width: $memory_stat%">
                      $memory_stat%
                    </div>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td>存储：</td>
        <td>
            $disk_html
        </td>
    </tr>
    <tr>
        <td>系统版本：</td>
        <td>$system_release</td>
    </tr>
    <tr>
        <td>开机时间：</td>
        <td>$boottime</td>
    </tr>
    <tr>
        <td>状态：</td>
        <td>
        $platform_alive
    </tr>
</table>
</div>
HTML;
            $info_box = new Box('主机信息', $info_html);
            $tab->add('主机信息', $info_box, 'base_info');

/*
            $xml_data = array(
                            'module' => 'system_info',
                            'func' => 'process',
                            'info' => array(
                                'dst_platform_ip' => $platform->platform_ip
                            )
                        );

            $socketClient = new \App\SocketClient(config('app.socket_local_host'), config('app.socket_local_port'), $xml_data);
            $socket_response = $socketClient->send();
            $socketClient->close();
*/
            $process_info = '';
            $process_box = new Box('进程信息', '进程信息');
            $tab->add('进程信息', $process_box, 'process');

            $network_box = new Box('网络信息', '网络信息');
            $tab->add('网络信息', $network_box, 'network');
            
            $content->row($tab);

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
        return Admin::grid(Platform::class, function (Grid $grid) {

            $grid->id('ID')->sortable();

            $grid->platform_name('主机名')->editable();
            $grid->platform_ip('IP地址')->editable();

            $grid->platform_sn('序列号');
            $grid->column('alive', '状态')->display(function($alive) {
                return $alive==1?'<span class="label label-success">Alive</span>':'<span class="label label-danger">Dead</span>';
            });

            $grid->created_at('添加时间');
            // $grid->updated_at();

            $grid->actions(function ($actions) {
                $id = $actions->getKey();
                $actions->disableEdit();
                $actions->disableDelete();
                $actions->append('<a href="platform/' . $id . '">查看</a>');
                /*$actions->append('&nbsp;|&nbsp;<a href="platform-process/' . $id . '">策略</a>');
                $actions->append('&nbsp;|&nbsp;<a href="platform-files/' . $id . '">文件</a>');*/
            });

            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });
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
        return Admin::form(Platform::class, function (Form $form) {

            $form->display('id', 'ID');

            $form->text('platform_name', '主机名');
            $form->text('platform_ip', 'IP地址');
            $form->text('platform_sn', '序列号');
            $form->text('alive', '状态');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }

}
