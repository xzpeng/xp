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

            //$actions_box = new Box('操作', '<a href="/admin/platform-add-user/' . $id . '">添加用户</a> | <a href="/admin/platform-add-process/' . $id . '">添加可执行策略</a> | <a href="/admin/platform-add-file/' . $id . '">添加文件策略</a>');
            //$content->row($actions_box);


            $tab = new Tab();

            if($platform_system_info = json_decode($platform->platform_system_info, true)) {
                $cpu_stat = isset($platform_system_info['cpu'])?$platform_system_info['cpu']:0;
                $memory_stat = ( isset($platform_system_info['memory'])&&isset($platform_system_info['memory']['available'])&&isset($platform_system_info['memory']['total']) )?round(($platform_system_info['memory']['available']*100/$platform_system_info['memory']['total']), 1):0;
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
            <!--
            <div class="col-sm-8">
                <div class="progress progress-sm">
                    <div class="progress-bar progress-bar-primary" role="progressbar" aria-valuenow="" aria-valuemin="0" aria-valuemax="100" style="width: 73.5%">
                      <span class="sr-only"></span>
                    </div>
                </div>
            </div>
            -->
        </td>
    </tr>
    <tr>
        <td>系统版本：</td>
        <td>$system_release</td>
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
            $tab->add('主机信息', $info_box);

            $process_box = new Box('进程信息', '进程信息');
            $tab->add('进程信息', $process_box);

            $network_box = new Box('网络信息', '网络信息');
            $tab->add('网络信息', $network_box);

/*
            $user_table_headers = ['用户名', '密码', '角色', '操作'];
            $user_table_rows = [];
            $user_rows = Platform::find($id)->strategies()->where('module', 'user_manage')->where('is_deleted', 0)->select(['id', 'info_username', 'info_passwd', 'info_role'])->get()->toArray();

            foreach ($user_rows as $key => $user_row) {
                $user_table_rows[$key][] = $user_row['info_username'];
                $user_table_rows[$key][] = $user_row['info_passwd'];
                $user_table_rows[$key][] = $user_row['info_role'];
                $user_table_rows[$key][] = '<a href="/admin/platform-del-user/' . $user_row['id'] . '">删除</a>';
            }

            $user_table = new Table($user_table_headers, $user_table_rows);
            $tab->add('用户管理', $user_table);

            $process_table_headers = ['程序名', '程序大小', '程序hash', '操作'];
            $process_table_rows = [];
            $process_rows = Platform::find($id)->strategies()->where('module', 'process_manage')->where('is_deleted', 0)->select(['id', 'info_process_name', 'info_process_size', 'info_process_hash'])->get()->toArray();
            
            foreach ($process_rows as $key => $process_row) {
                $process_table_rows[$key][] = $process_row['info_process_name'];
                $process_table_rows[$key][] = $process_row['info_process_size'];
                $process_table_rows[$key][] = $process_row['info_process_hash'];
                $process_table_rows[$key][] = '<a href="/admin/platform-del-process/' . $process_row['id'] . '">删除</a>';
            }

            $process_table = new Table($process_table_headers, $process_table_rows);
            $tab->add('程序管理', $process_table);

            $file_table_headers = ['文件名', '文件大小', '文件hash', '文件操作_opt', '生效时间', '结束时间', '操作'];
            $file_table_rows = [];
            $file_rows = Platform::find($id)->strategies()->where('module', 'file_manage')->where('is_deleted', 0)->select(['id', 'info_file_name', 'info_file_size', 'info_file_hash', 'info_file_opt', 'info_active_starttime', 'info_active_endtime'])->get()->toArray();

            foreach ($file_rows as $key => $file_row) {
                $file_table_rows[$key][] = $file_row['info_file_name'];
                $file_table_rows[$key][] = $file_row['info_file_size'];
                $file_table_rows[$key][] = $file_row['info_file_hash'];
                $file_table_rows[$key][] = $file_row['info_file_opt'];
                $file_table_rows[$key][] = $file_row['info_active_starttime'];
                $file_table_rows[$key][] = $file_row['info_active_endtime'];
                $file_table_rows[$key][] = '<a href="/admin/platform-del-file/' . $file_row['id'] . '">删除</a>';
            }

            $file_table = new Table($file_table_headers, $file_table_rows);
            $tab->add('文件管理', $file_table);
            */

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
                $actions->append('&nbsp;|&nbsp;<a href="platform-process/' . $id . '">策略</a>');
                $actions->append('&nbsp;|&nbsp;<a href="platform-files/' . $id . '">文件</a>');
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
