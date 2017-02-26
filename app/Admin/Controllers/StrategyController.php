<?php

namespace App\Admin\Controllers;

use App\Models\Strategy;
use App\Models\Host;
use Illuminate\Http\Request;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

use Auth;

class StrategyController extends Controller
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
        return Admin::grid(Strategy::class, function (Grid $grid) {

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
    protected function form()
    {
        return Admin::form(Strategy::class, function (Form $form) {

            $form->display('id', 'ID');

            $form->select('host_id')->options(function($id) {
                return Host::options($id);
            });

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }


    protected function formAddUser($id)
    {
        return Admin::form(Strategy::class, function (Form $form) use($id) {

            $host = Host::find($id);

            $form->display('id', 'ID');
            $form->hidden('host_id')->value($id);

            $form->display('host_name', '调度主机')->value($host->host_name);
            $form->display('host_ip', '主机IP')->value($host->host_ip);
            $form->display('host_sn', '主机SN')->value($host->host_sn);
            $form->divider();
            $form->text('username', '用户名');
            $form->text('passwd', '密码');
            $form->text('role', '角色');

            
            // $form->ignore();
            // $form->setWidth(10, 2);
            $form->setAction('/admin/host-add-users');
        });
    }

    protected function formAddProcess($id)
    {
        return Admin::form(Strategy::class, function (Form $form) use($id) {

            $host = Host::find($id);

            $form->display('id', 'ID');
            $form->hidden('host_id')->value($id);

            $form->display('host_name', '调度主机')->value($host->host_name);
            $form->display('host_ip', '主机IP')->value($host->host_ip);
            $form->display('host_sn', '主机SN')->value($host->host_sn);
            $form->divider();
            $form->text('process_name', '程序名');
            $form->text('process_size', '大小');
            $form->text('process_hash', 'Hash值');

            
            // $form->ignore();
            // $form->setWidth(10, 2);
            $form->setAction('/admin/host-add-process');
        });
    }

    protected function formAddFile($id)
    {
        return Admin::form(Strategy::class, function (Form $form) use($id) {

            $host = Host::find($id);

            $form->display('id', 'ID');
            $form->hidden('host_id')->value($id);

            $form->display('host_name', '调度主机')->value($host->host_name);
            $form->display('host_ip', '主机IP')->value($host->host_ip);
            $form->display('host_sn', '主机SN')->value($host->host_sn);
            $form->divider();
            $form->text('file_name', '文件名');
            $form->text('file_size', '大小');
            $form->text('file_hash', 'Hash值');
            $form->text('file_opt', '操作');
            $form->datetime('active_starttime', '生效时间');
            $form->datetime('active_endtime', '生效时间');

            
            // $form->ignore();
            // $form->setWidth(10, 2);
            $form->setAction('/admin/host-add-file');
        });
    }


    public function addUser($id)
    {
        return Admin::content(function (Content $content) use($id) {

            $content->header('header');
            $content->description('description');

            $content->body($this->formAddUser($id));
        });
    }

    public function postAddUser(Request $request)
    {
        $request_data = $request->input();

        $host = Host::find($request_data['host_id']);
        $xml_data = array(
                        'module' => 'user_manage',
                        'func' => 'add',
                        'info' => array(
                            'username' => $request_data['username'],
                            'passwd' => $request_data['passwd'],
                            'role' => $request_data['role'],
                            'platform_name' => $host->host_name,
                            'platform_sn' => $host->host_sn,
                            'platform_ip' => $host->host_ip,
                        )
                    );

        $socketClient = new \App\SocketClient($host->host_ip, 9003, $xml_data);
        $socket_response = $socketClient->send();
        $socketClient->close();

        if($socket_response) {
            $strategy = new Strategy;

            $strategy->host_id = $host->id;
            $strategy->author = Admin::user()->id;
            $strategy->module = 'user_manage';
            $strategy->func = 'add';
            $strategy->info_username = $request_data['username'];
            $strategy->info_passwd = $request_data['passwd'];
            $strategy->info_role = $request_data['role'];
            $strategy->info_platform_name = $host->host_name;
            $strategy->info_platform_sn = $host->host_sn;
            $strategy->info_platform_ip = $host->host_ip;

            $strategy->save();
        }

        return redirect('/admin/host/' . $request_data['host_id']);
    }

    public function delUser($id)
    {
        $strategy = Strategy::find($id);
        
        $host = Host::find($strategy->host_id);

        $xml_data = array(
                        'module' => 'user_manage',
                        'func' => 'del',
                        'info' => array(
                            'username' => $strategy->info_username,
                            'passwd' => '',
                            'role' => '',
                            'platform_name' => $host->host_name,
                            'platform_sn' => $host->host_sn,
                            'platform_ip' => $host->host_ip,
                        )
                    );

        $socketClient = new \App\SocketClient($host->host_ip, 9003, $xml_data);
        $socket_response = $socketClient->send();
        $socketClient->close();

        if($socket_response) {
            $strategy->delete();
        }

        return redirect('/admin/host/' . $request_data['host_id']);
    }

    public function addProcess($id)
    {
        return Admin::content(function (Content $content) use($id) {

            $content->header('header');
            $content->description('description');

            $content->body($this->formAddProcess($id));
        });
    }

    public function postAddProcess(Request $request)
    {
        $request_data = $request->input();

        $host = Host::find($request_data['host_id']);
        $xml_data = array(
                        'module' => 'process_manage',
                        'func' => 'add',
                        'info' => array(
                            'process_name' => $request_data['process_name'],
                            'process_size' => $request_data['process_size'],
                            'process_hash' => $request_data['process_hash'],
                            'platform_name' => $host->host_name,
                            'platform_sn' => $host->host_sn,
                            'platform_ip' => $host->host_ip,
                        )
                    );

        $socketClient = new \App\SocketClient($host->host_ip, 9003, $xml_data);
        $socket_response = $socketClient->send();
        $socketClient->close();

        if($socket_response) {
            $strategy = new Strategy;

            $strategy->host_id = $host->id;
            $strategy->author = Admin::user()->id;
            $strategy->module = 'process_manage';
            $strategy->func = 'add';
            $strategy->info_process_name = $request_data['process_name'];
            $strategy->info_process_size = $request_data['process_size'];
            $strategy->info_process_hash = $request_data['process_hash'];
            $strategy->info_platform_name = $host->host_name;
            $strategy->info_platform_sn = $host->host_sn;
            $strategy->info_platform_ip = $host->host_ip;

            $rst = $strategy->save();
        }

        return redirect('/admin/host/' . $request_data['host_id']);
    }

    public function delProcess($id)
    {
        $strategy = Strategy::find($id);
        
        $host = Host::find($strategy->host_id);

        $xml_data = array(
                        'module' => 'file_manage',
                        'func' => 'del',
                        'info' => array(
                            'process_name' => $strategy->info_process_name,
                            'process_size' => $strategy->info_process_size,
                            'process_hash' => $strategy->info_process_hash,
                            'platform_name' => $host->host_name,
                            'platform_sn' => $host->host_sn,
                            'platform_ip' => $host->host_ip,
                        )
                    );

        $socketClient = new \App\SocketClient($host->host_ip, 9003, $xml_data);
        $socket_response = $socketClient->send();
        $socketClient->close();

        if($socket_response) {
            $strategy->delete();
        }

        return redirect('/admin/host/' . $strategy->host_id);
    }

    public function addFile($id)
    {
        return Admin::content(function (Content $content) use($id) {

            $content->header('header');
            $content->description('description');

            $content->body($this->formAddFile($id));
        });
    }
    
    public function postAddFile(Request $request)
    {
        $request_data = $request->input();

        $host = Host::find($request_data['host_id']);
        $xml_data = array(
                        'module' => 'process_manage',
                        'func' => 'add',
                        'info' => array(
                            'file_name' => $request_data['file_name'],
                            'file_size' => $request_data['file_size'],
                            'file_hash' => $request_data['file_hash'],
                            'file_opt' => $request_data['file_opt'],
                            'active_starttime' => $request_data['active_starttime'],
                            'active_endtime' => $request_data['active_endtime'],
                            'platform_name' => $host->host_name,
                            'platform_sn' => $host->host_sn,
                            'platform_ip' => $host->host_ip,
                        )
                    );

        $socketClient = new \App\SocketClient($host->host_ip, 9003, $xml_data);
        $socket_response = $socketClient->send();
        $socketClient->close();

        if($socket_response) {
            $strategy = new Strategy;

            $strategy->host_id = $host->id;
            $strategy->author = Admin::user()->id;
            $strategy->module = 'file_manage';
            $strategy->func = 'add';
            $strategy->info_file_name = $request_data['file_name'];
            $strategy->info_file_size = $request_data['file_size'];
            $strategy->info_file_hash = $request_data['file_hash'];
            $strategy->info_file_opt = $request_data['file_opt'];
            $strategy->info_active_starttime = $request_data['active_starttime'];
            $strategy->info_active_endtime = $request_data['active_endtime'];
            $strategy->info_platform_name = $host->host_name;
            $strategy->info_platform_sn = $host->host_sn;
            $strategy->info_platform_ip = $host->host_ip;

            $strategy->save();
        }

        return redirect('/admin/host/' . $request_data['host_id']);
    }

    public function delFile($id)
    {
        $strategy = Strategy::find($id);
        
        $host = Host::find($strategy->host_id);

        $xml_data = array(
                        'module' => 'file_manage',
                        'func' => 'del',
                        'info' => array(
                            'file_name' => $strategy->info_file_name,
                            'file_size' => $strategy->info_file_size,
                            'file_hash' => $strategy->info_file_hash,
                            'file_opt' => $strategy->info_file_opt,
                            'active_starttime' => $strategy->info_active_starttime,
                            'active_endtime' => $strategy->info_active_endtime,
                            'platform_name' => $host->host_name,
                            'platform_sn' => $host->host_sn,
                            'platform_ip' => $host->host_ip,
                        )
                    );

        $socketClient = new \App\SocketClient($host->host_ip, 9003, $xml_data);
        $socket_response = $socketClient->send();
        $socketClient->close();

        if($socket_response) {
            $strategy->delete();
        }

        return redirect('/admin/host/' . $strategy->host_id);
    }
}
