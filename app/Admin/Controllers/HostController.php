<?php

namespace App\Admin\Controllers;

use App\Models\Host;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Tab;
use Encore\Admin\Widgets\Table;

class HostController extends Controller
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
            $host = Host::find($id);

            $content->header('主机管理');
            $content->description('主机信息查看、管理……');

            $actions_box = new Box('操作', '<a href="/admin/host-add-user/' . $id . '">添加用户</a> | <a href="/admin/host-add-process/' . $id . '">添加可执行策略</a> | <a href="/admin/host-add-file/' . $id . '">添加文件策略</a>');
            $content->row($actions_box);


            $tab = new Tab();

            $info_html = <<<HTML
<p>主机名： $host->name </p>
<p>IP地址：$host->ip </p>
<p>序列号：$host->sn </p>
<p>CPU：$host->cpu </p>
<p>内存：$host->memory </p>
<p>存储：$host->disk </p>
<p>状态：$host->status </p>

HTML;
            $info_box = new Box('基本信息', $info_html);
            $tab->add('主机信息', $info_box);


            $user_table_headers = ['username', 'passwd', 'role', 'action'];
            $user_table_rows = [];
            $user_rows = Host::find($id)->strategies()->where('module', 'user_manage')->where('is_deleted', 0)->select(['id', 'info_username', 'info_passwd', 'info_role'])->get()->toArray();

            foreach ($user_rows as $key => $user_row) {
                $user_table_rows[$key][] = $user_row['info_username'];
                $user_table_rows[$key][] = $user_row['info_passwd'];
                $user_table_rows[$key][] = $user_row['info_role'];
                $user_table_rows[$key][] = '<a href="/admin/host-del-user/' . $user_row['id'] . '">删除</a>';
            }

            $user_table = new Table($user_table_headers, $user_table_rows);
            $tab->add('用户管理', $user_table);

            $process_table_headers = ['process_name', 'process_size', 'process_hash', 'action'];
            $process_table_rows = [];
            $process_rows = Host::find($id)->strategies()->where('module', 'process_manage')->where('is_deleted', 0)->select(['id', 'info_process_name', 'info_process_size', 'info_process_hash'])->get()->toArray();
            
            foreach ($process_rows as $key => $process_row) {
                $process_table_rows[$key][] = $process_row['info_process_name'];
                $process_table_rows[$key][] = $process_row['info_process_size'];
                $process_table_rows[$key][] = $process_row['info_process_hash'];
                $process_table_rows[$key][] = '<a href="/admin/host-del-process/' . $process_row['id'] . '">删除</a>';
            }

            $process_table = new Table($process_table_headers, $process_table_rows);
            $tab->add('程序管理', $process_table);

            $file_table_headers = ['file_name', 'file_size', 'file_hash', 'file_opt', 'active_starttime', 'active_endtime', 'action'];
            $file_table_rows = [];
            $file_rows = Host::find($id)->strategies()->where('module', 'file_manage')->where('is_deleted', 0)->select(['id', 'info_file_name', 'info_file_size', 'info_file_hash', 'info_file_opt', 'info_active_starttime', 'info_active_endtime'])->get()->toArray();

            foreach ($file_rows as $key => $file_row) {
                $file_table_rows[$key][] = $file_row['info_file_name'];
                $file_table_rows[$key][] = $file_row['info_file_size'];
                $file_table_rows[$key][] = $file_row['info_file_hash'];
                $file_table_rows[$key][] = $file_row['info_file_opt'];
                $file_table_rows[$key][] = $file_row['info_active_starttime'];
                $file_table_rows[$key][] = $file_row['info_active_endtime'];
                $file_table_rows[$key][] = '<a href="/admin/host-del-file/' . $file_row['id'] . '">删除</a>';
            }

            $file_table = new Table($file_table_headers, $file_table_rows);
            $tab->add('文件管理', $file_table);
            

            $content->row($tab);

        });


        /*$host = Host::find($id);
        echo $host->name, $host->ip;*/
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
        return Admin::grid(Host::class, function (Grid $grid) {

            $grid->id('ID')->sortable();

            $grid->name('主机名')->editable();
            $grid->ip('IP地址')->editable();

            $states = [
                'on' => ['text' => 'Alive'],
                'off' => ['text' => 'Dead'],
            ];

            $grid->sn('序列号');
            $grid->cpu('CPU')->progressBar();
            $grid->memory('内存')->progressBar();
            $grid->disk('存储')->progressBar('warning');
            $grid->status('状态')->switch($states);

            $grid->created_at();
            $grid->updated_at();

            $grid->actions(function ($actions) {
                $id = $actions->getKey();
                $actions->disableEdit();
                $actions->append('<a href="host/' . $id . '"><i class="fa fa-eye"></i></a>');
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Host::class, function (Form $form) {

            $form->display('id', 'ID');

            $form->text('name', '主机名');
            $form->text('ip', 'IP地址');
            $form->text('sn', '序列号');
            $form->text('status', '状态');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }

}
