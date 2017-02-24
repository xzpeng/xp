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

            $form->display('host_name', '调度主机')->value($host->name);
            $form->display('host_ip', '主机IP')->value($host->ip);
            $form->display('host_sn', '主机SN')->value($host->sn);
            $form->divider();
            $form->text('username', '用户名');
            $form->text('passwd', '密码');
            $form->text('role', '角色');

            
            // $form->ignore();
            // $form->setWidth(10, 2);
            $form->setAction('/admin/host-add-users');
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
                            'platform_name' => $host->name,
                            'platform_sn' => $host->sn,
                            'platform_ip' => $host->ip,
                        )
                    );

        $socketClient = new \App\SocketClient($host->ip, 9003, $xml_data);
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
            $strategy->info_platform_name = $host->name;
            $strategy->info_platform_sn = $host->sn;
            $strategy->info_platform_ip = $host->ip;

            $strategy->save();
        }

        return redirect('/admin/host/' . $request_data['host_id']);
    }

    public function delUser($id)
    {
        dd(Admin::user()->id);
        echo 'delUser',$id;
    }

    public function addProcess($id)
    {
        echo 'addProcess',$id;
    }

    public function delProcess($id)
    {
        echo 'delProcess',$id;
    }

    public function addFile($id)
    {
        echo 'addFile',$id;
    }

    public function delFile($id)
    {
        echo 'delFile',$id;
    }
}
