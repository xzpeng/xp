<?php

namespace App\Admin\Controllers;

use App\Models\Strategy;
use App\Models\Platform;
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

            $form->select('platform_id')->options(function($id) {
                return Platform::options($id);
            });

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }


    protected function formAddUser($id)
    {
        return Admin::form(Strategy::class, function (Form $form) use($id) {

            $platform = Platform::find($id);

            $form->display('id', 'ID');
            $form->hidden('platform_id')->value($id);

            $form->display('platform_name', '调度主机')->value($platform->platform_name);
            $form->display('platform_ip', '主机IP')->value($platform->platform_ip);
            $form->display('platform_sn', '主机SN')->value($platform->platform_sn);
            $form->divider();
            $form->text('username', '用户名');
            $form->text('passwd', '密码');
            $form->text('role', '角色');

            
            // $form->ignore();
            // $form->setWidth(10, 2);
            $form->setAction('/admin/platform-add-users');
        });
    }

    protected function formAddProcess($id)
    {
        return Admin::form(Strategy::class, function (Form $form) use($id) {

            $platform = Platform::find($id);

            $form->display('id', 'ID');
            $form->hidden('platform_id')->value($id);

            $form->display('platform_name', '调度主机')->value($platform->platform_name);
            $form->display('platform_ip', '主机IP')->value($platform->platform_ip);
            $form->display('platform_sn', '主机SN')->value($platform->platform_sn);
            $form->divider();
            $form->text('process_name', '程序名');
            $form->text('process_size', '大小');
            $form->text('process_hash', 'Hash值');

            
            // $form->ignore();
            // $form->setWidth(10, 2);
            $form->setAction('/admin/platform-add-process');
        });
    }

    protected function formAddFile($id)
    {
        return Admin::form(Strategy::class, function (Form $form) use($id) {

            $platform = Platform::find($id);

            $form->display('id', 'ID');
            $form->hidden('platform_id')->value($id);

            $form->display('platform_name', '调度主机')->value($platform->platform_name);
            $form->display('platform_ip', '主机IP')->value($platform->platform_ip);
            $form->display('platform_sn', '主机SN')->value($platform->platform_sn);
            $form->divider();
            $form->text('file_name', '文件名');
            $form->text('file_size', '大小');
            $form->text('file_hash', 'Hash值');
            $form->text('file_opt', '操作');
            $form->datetime('active_starttime', '生效时间');
            $form->datetime('active_endtime', '生效时间');

            
            // $form->ignore();
            // $form->setWidth(10, 2);
            $form->setAction('/admin/platform-add-file');
        });
    }


    public function addUser($id, $msg='')
    {
        return Admin::content(function (Content $content) use($id, $msg) {

            $content->header('添加用户');
            if($msg){
                $content->description($msg);
            }

            $content->body($this->formAddUser($id));
        });
    }

    public function postAddUser(Request $request)
    {
        $request_data = $request->input();

        $platform = Platform::find($request_data['platform_id']);
        $xml_data = array(
                        'module' => 'user_manage',
                        'func' => 'add',
                        'info' => array(
                            'username' => $request_data['username'],
                            'passwd' => $request_data['passwd'],
                            'role' => $request_data['role'],
                            'platform_name' => $platform->platform_name,
                            'platform_sn' => $platform->platform_sn,
                            'platform_ip' => $platform->platform_ip,
                        )
                    );

        $socketClient = new \App\SocketClient($platform->platform_ip, config('app.socket_remote_port'), $xml_data);
        $socket_response = $socketClient->send();
        $socketClient->close();

        if($socket_response) {
            $strategy = new Strategy;

            $strategy->platform_id = $platform->id;
            $strategy->author = Admin::user()->id;
            $strategy->module = 'user_manage';
            $strategy->func = 'add';
            $strategy->info_username = $request_data['username'];
            $strategy->info_passwd = $request_data['passwd'];
            $strategy->info_role = $request_data['role'];
            $strategy->info_platform_name = $platform->platform_name;
            $strategy->info_platform_sn = $platform->platform_sn;
            $strategy->info_platform_ip = $platform->platform_ip;

            $strategy->save();
        }

        return redirect('/admin/platform-add-user/' . $request_data['platform_id'] . '/用户' . $request_data['username'] . '添加成功!');
    }

    public function delUser($id)
    {
        $strategy = Strategy::find($id);
        
        $platform = Platform::find($strategy->platform_id);

        $xml_data = array(
                        'module' => 'user_manage',
                        'func' => 'del',
                        'info' => array(
                            'username' => $strategy->info_username,
                            'passwd' => '',
                            'role' => '',
                            'platform_name' => $platform->platform_name,
                            'platform_sn' => $platform->platform_sn,
                            'platform_ip' => $platform->platform_ip,
                        )
                    );

        $socketClient = new \App\SocketClient($platform->platform_ip, config('app.socket_remote_port'), $xml_data);
        $socket_response = $socketClient->send();
        $socketClient->close();

        if($socket_response) {
            $strategy->delete();
        }

        return redirect('/admin/platform/' . $request_data['platform_id']);
    }

    public function addProcess($id, $msg='')
    {
        return Admin::content(function (Content $content) use($id, $msg) {

            $content->header('添加策略');
            if($msg){
                $content->description( base64_decode($msg));
            }

            $content->body($this->formAddProcess($id));
        });
    }

    public function postAddProcess(Request $request)
    {
        $request_data = $request->input();

        $platform = Platform::find($request_data['platform_id']);
        $xml_data = array(
                        'module' => 'process_manage',
                        'func' => 'add',
                        'info' => array(
                            'process_name' => $request_data['process_name'],
                            'process_size' => $request_data['process_size'],
                            'process_hash' => $request_data['process_hash'],
                            'platform_name' => $platform->platform_name,
                            'platform_sn' => $platform->platform_sn,
                            'platform_ip' => $platform->platform_ip,
                        )
                    );

        $socketClient = new \App\SocketClient($platform->platform_ip, config('app.socket_remote_port'), $xml_data);
        $socket_response = $socketClient->send();
        $socketClient->close();

        if($socket_response) {
            $strategy = new Strategy;

            $strategy->platform_id = $platform->id;
            $strategy->author = Admin::user()->id;
            $strategy->module = 'process_manage';
            $strategy->func = 'add';
            $strategy->info_process_name = $request_data['process_name'];
            $strategy->info_process_size = $request_data['process_size'];
            $strategy->info_process_hash = $request_data['process_hash'];
            $strategy->info_platform_name = $platform->platform_name;
            $strategy->info_platform_sn = $platform->platform_sn;
            $strategy->info_platform_ip = $platform->platform_ip;

            $rst = $strategy->save();
        }

        return redirect('/admin/platform-add-process/' . $request_data['platform_id'] . '/策略' . base64_encode($request_data['process_name']) . '添加成功!');
    }

    public function delProcess($id)
    {
        $strategy = Strategy::find($id);
        
        $platform = Platform::find($strategy->platform_id);

        $xml_data = array(
                        'module' => 'process_manage',
                        'func' => 'del',
                        'info' => array(
                            'process_name' => $strategy->info_process_name,
                            'process_size' => $strategy->info_process_size,
                            'process_hash' => $strategy->info_process_hash,
                            'platform_name' => $platform->platform_name,
                            'platform_sn' => $platform->platform_sn,
                            'platform_ip' => $platform->platform_ip,
                        )
                    );

        $socketClient = new \App\SocketClient($platform->platform_ip, config('app.socket_remote_port'), $xml_data);
        $socket_response = $socketClient->send();
        $socketClient->close();

        if($socket_response) {
            $strategy->delete();
        }

        return redirect('/admin/platform/' . $strategy->platform_id);
    }

    public function addFile($id, $msg='')
    {
        return Admin::content(function (Content $content) use($id, $msg) {

            $content->header('添加文件');
            if($msg){
                $content->description($msg);
            }

            $content->body($this->formAddFile($id));
        });
    }
    
    public function postAddFile(Request $request)
    {
        $request_data = $request->input();

        $platform = Platform::find($request_data['platform_id']);
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
                            'platform_name' => $platform->platform_name,
                            'platform_sn' => $platform->platform_sn,
                            'platform_ip' => $platform->platform_ip,
                        )
                    );

        $socketClient = new \App\SocketClient($platform->platform_ip, config('app.socket_remote_port'), $xml_data);
        $socket_response = $socketClient->send();
        $socketClient->close();

        if($socket_response) {
            $strategy = new Strategy;

            $strategy->platform_id = $platform->id;
            $strategy->author = Admin::user()->id;
            $strategy->module = 'file_manage';
            $strategy->func = 'add';
            $strategy->info_file_name = $request_data['file_name'];
            $strategy->info_file_size = $request_data['file_size'];
            $strategy->info_file_hash = $request_data['file_hash'];
            $strategy->info_file_opt = $request_data['file_opt'];
            $strategy->info_active_starttime = $request_data['active_starttime'];
            $strategy->info_active_endtime = $request_data['active_endtime'];
            $strategy->info_platform_name = $platform->platform_name;
            $strategy->info_platform_sn = $platform->platform_sn;
            $strategy->info_platform_ip = $platform->platform_ip;

            $strategy->save();
        }

        return redirect('/admin/platform-add-file/' . $request_data['platform_id'] . '/文件' . $request_data['file_name'] . '添加成功!');
    }

    public function delFile($id)
    {
        $strategy = Strategy::find($id);
        
        $platform = Platform::find($strategy->platform_id);

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
                            'platform_name' => $platform->platform_name,
                            'platform_sn' => $platform->platform_sn,
                            'platform_ip' => $platform->platform_ip,
                        )
                    );

        $socketClient = new \App\SocketClient($platform->platform_ip, config('app.socket_remote_port'), $xml_data);
        $socket_response = $socketClient->send();
        $socketClient->close();

        if($socket_response) {
            $strategy->delete();
        }

        return redirect('/admin/platform/' . $strategy->platform_id);
    }
}
