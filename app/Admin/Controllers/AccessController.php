<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\ModelForm;

use App\Models\Platform;
use App\Models\Access;

use Illuminate\Http\Request;

use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Tab;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Widgets\Form;

class AccessController extends Controller
{
    use ModelForm;

    public function index() {
        return Admin::content(function (Content $content) {

            $content->header('访问控制');
            
            $html_add_button = '<div class="pull-right">
            <div class="btn-group pull-right" style="margin-right: 10px">
                <a href="/admin/access-platform-list/" class="btn btn-sm btn-success">
                    <i class="fa fa-save"></i>&nbsp;&nbsp;添加策略
                </a>
            </div>
            </div>';
            $content->row( (new Box('操作', $html_add_button))->style('info')->solid() );

            $content->row($this->gridAll());
        });
    }

    public function accessPlatformList() {
        return Admin::content(function (Content $content) {

            $content->header('访问控制');
            $content->description('主机列表');

            $content->body($this->grid());
        });
    }


    public function show($pid) {
        return Admin::content(function(Content $content) use($pid) {
            $platform = Platform::find($pid);
            $content->header('访问控制');
            $content->description('主机: ' . $platform->platform_name);

            if ($platform->alive==1) {
                $html_add_button = '
                <div class="pull-left">
                    <span class="label label-success">主机正常</span>
                </div>
                <div class="pull-right">
                    <div class="btn-group pull-right" style="margin-right: 10px">
                        <a href="/admin/access-whitelist-add/' . $pid . '" class="btn btn-sm btn-success">
                            <i class="fa fa-save"></i>&nbsp;&nbsp;新增
                        </a>
                    </div>
                </div>';
            } else {
                $html_add_button = '<div class="pull-right">
                <span class="label label-danger">主机不在线</span>
                <div class="btn-group pull-right" style="margin-right: 10px">
                    <a href="#" class="btn btn-sm btn-danger" disabled="disabled">
                        <i class="fa fa-save"></i>&nbsp;&nbsp;新增
                    </a>
                </div>
                </div>';
            }
            $content->row( (new Box('操作', $html_add_button))->style('info')->solid() );
            $content->row($this->gridAccesss($pid));
        });
    }


    public function accessWhitelistAdd($pid) {
        return Admin::content(function (Content $content) use($pid) {
            $content->header('访问控制');
            $content->description('当前目录：/');

            $form = new Form();
            $form->action('/admin/search-accesses/' . $pid);
            $form->method('get');
            $form->text('parent_folder','')->default('/');
            $content->row( (new Box('查询目录', $form))->style('info')->solid() );

            $platform = Platform::find($pid);

            $headers = ['', '目录/文件'];
            $rows = [];

            if ($platform) {
                
                $xml_data = array(
                            'module' => 'system_info',
                            'func' => 'query_dir',
                            'info' => array(
                                'query_path' => '/'
                            )
                        );

                $socketClient = new \App\SocketClient($platform->platform_ip, config('app.socket_remote_port'), $xml_data);
                $socket_response = $socketClient->send();
                $socketClient->close();
                
            /*    
                $xml = '<?xml version="1.0" encoding="UTF-8"?><Response><result>Success</result><message><item><file_name>/tmp/.keystone_install_lock</file_name><file_type>1</file_type></item><item><file_name>/tmp/aprfIczf9</file_name><file_type>1</file_type></item><item><file_name>/tmp/com.apple.launchd.1glvZv3cOU</file_name><file_type>2</file_type></item><item><file_name>/tmp/com.apple.launchd.8XEBJ773jd</file_name><file_type>2</file_type></item><item><file_name>/tmp/com.apple.launchd.JReff3ZINe</file_name><file_type>2</file_type></item><item><file_name>/tmp/com.apple.launchd.r0BfkWU9j4</file_name><file_type>2</file_type></item><item><file_name>/tmp/com.apple.launchd.Wgym89EbHN</file_name><file_type>2</file_type></item><item><file_name>/tmp/com.apple.launchd.yC2qRjsFOh</file_name><file_type>2</file_type></item><item><file_name>/tmp/cvcd</file_name><file_type>2</file_type></item><item><file_name>/tmp/KSOutOfProcessFetcher.CifFMeoplW</file_name><file_type>2</file_type></item></message></Response>';
                $socket_response = new \SimpleXMLElement($xml);
*/
                if( strtolower($socket_response->result)=='success' ) {
                    $accesses = $socket_response->message->item;
                    foreach ($accesses as $access) {
                        if ($access->file_type==2) {
                            $rows[] = ['<input type="checkbox" name="accesses[]" value="' . base64_encode($access->file_name) . '"/>', '<a href="/admin/search-accesses/' . $pid . '?parent_folder=' . $access->file_name . '">' . $access->file_name . '</a>'];
                        } else {
                            $rows[] = ['<input type="checkbox" name="accesses[]" value="' . base64_encode($access->file_name) . '"/>', $access->file_name];
                        }
                    }
                }
            }

            $table = new Table($headers, $rows);

            $form = new Form();
            $form->action('/admin/post-add-access');
            $form->method('post');
            $form->hidden('platform_id')->default($pid);
            $form->checkbox('subs', '主体')->options(['/usr/bin/vim'=>'Vim','/bin/nano'=>'Nano']);
            $form->dateTimeRange('active_starttime', 'active_endtime', '生效时间');
            $form->html($table->render());
            $content->row(new Box('添加访问控制', $form));
        });

    }


    public function accessWhitelistDel($pid, $id) {
        $accessObj = Access::find($id);
        
        if($accessObj) {
            $platform = Platform::find($accessObj->platform_id);

            $xml_data = array(
                            'module' => 'file_manage',
                            'func' => 'del_dac',
                            'info' => array(
                                'sub_name' => $accessObj->sub_name,
                                'sub_hash' => $accessObj->sub_hash,
                                'file_name' => $accessObj->folder_name,
                                'file_hash' => $accessObj->folder_hash,
                                'file_opt' => $accessObj->folder_op,
                                'group_name' => $accessObj->group_name,
                                'active_starttime' => $accessObj->active_starttime,
                                'active_endtime' => $accessObj->active_endtime,
                            )
                        );

            $socketClient = new \App\SocketClient($platform->platform_ip, config('app.socket_remote_port'), $xml_data);
            
            $socket_response = $socketClient->send();
            $socketClient->close();

            if( strtolower($socket_response->result)=='success' ) {
                $accessObj->delete();
                return redirect('/admin/view-access/' . $pid);
            }

            return back();
        }
    }


    public function search($pid, Request $request) {
        $parent_folder = $request->input('parent_folder', '/');
        return Admin::content(function (Content $content) use($pid,$parent_folder) {
            $content->header('访问控制');
            $content->description('当前目录：' . $parent_folder);


            $form = new Form();
            $form->action('/admin/search-accesses/' . $pid);
            $form->method('get');
            $form->text('parent_folder','')->default($parent_folder);
            $content->row( (new Box('查询目录', $form))->style('info')->solid() );

            $platform = Platform::find($pid);

            $headers = ['目录/文件'];
            $rows = [];

            if ($platform) {
                
                $xml_data = array(
                            'module' => 'system_info',
                            'func' => 'query_dir',
                            'info' => array(
                                'query_path' => $parent_folder
                            )
                        );

                $socketClient = new \App\SocketClient($platform->platform_ip, config('app.socket_remote_port'), $xml_data);
                $socket_response = $socketClient->send();
                $socketClient->close();

                if( strtolower($socket_response->result)=='success' ) {
                    $accesses = $socket_response->message->item;
                    foreach ($accesses as $access) {
                        if ($access->file_type==2) {
                            $rows[] = ['<input type="checkbox" name="accesses[]" value="' . base64_encode($access->file_name) . '"/>', '<a href="/admin/search-accesses/' . $pid . '?parent_folder=' . $access->file_name . '">' . $access->file_name . '</a>'];
                        } else {
                            $rows[] = ['<input type="checkbox" name="accesses[]" value="' . base64_encode($access->file_name) . '"/>', $access->file_name];
                        }
                    }
                }
            }

            $table = new Table($headers, $rows);

            $form = new Form();
            $form->action('/admin/post-add-access');
            $form->method('post');
            $form->hidden('platform_id')->default($pid);
            $form->checkbox('subs', '主体')->options(['/usr/bin/vim'=>'Vim','/bin/nano'=>'Nano']);
            $form->dateTimeRange('active_starttime', 'active_endtime', '生效时间');
            $form->html($table->render());
            $content->row(new Box('添加访问控制', $form));
        });
    }

    public function postAddWhitelist(Request $request)
    {
        $accesses = $request->input('accesses');
        $platform_id = $request->input('platform_id');
        $subs = $request->input('subs');
        $active_starttime = $request->input('active_starttime');
        $active_endtime = $request->input('active_endtime');

        if ( $accesses && $platform_id && $subs) {

            $platform = Platform::find($platform_id);

            foreach($subs as $sub) {
                if ($sub == '') {
                    continue;
                }
                $sub_hash = hash('md5', $sub);
                foreach ($accesses as $access) {

                    $access = base64_decode($access);
                    $access_hash = hash('md5', $access);
                    $xml_data = array(
                                    'module' => 'file_manage',
                                    'func' => 'add_dac',
                                    'info' => array(
                                        'sub_name' => $sub,
                                        'sub_hash' => $sub_hash,
                                        'file_name' => $access,
                                        'file_size' => 0,
                                        'file_hash' => $access_hash,
                                        'file_opt' => 'read',
                                        'group_name' => '',
                                        'active_starttime' => $active_starttime,
                                        'active_endtime' => $active_endtime,
                                    )
                                );

                    $socketClient = new \App\SocketClient($platform->platform_ip, config('app.socket_remote_port'), $xml_data);
                    
                    $socket_response = $socketClient->send();
                    $socketClient->close();

                    if( strtolower($socket_response->result)=='success' ) {
                        $accessObj = new Access;
                        $accessObj->platform_id = $platform_id;
                        $accessObj->sub_name = $sub;
                        $accessObj->sub_hash = $sub_hash;
                        $accessObj->folder_name = $access;
                        $accessObj->folder_hash = $access_hash;
                        $accessObj->active_starttime = $active_starttime;
                        $accessObj->active_endtime = $active_endtime;
                        $accessObj->folder_op = 'read';

                        $accessObj->save();
                    }
                }
            }
            return redirect('/admin/view-access/' . $platform_id);
        } else {
            return redirect('/admin/view-access/' . $platform_id);
        }
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Platform::class, function (Grid $grid) {

            $grid->platform_name('主机名');
            $grid->platform_ip('IP地址');

            $grid->actions(function ($actions) {
                $id = $actions->getKey();
                $actions->disableEdit();
                $actions->disableDelete();
                $actions->append('<a href="view-access/' . $id . '">查看</a>');
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


    protected function gridAll()
    {
        return Admin::grid(Access::class, function (Grid $grid) {
            $grid->sub_name('主体');
            $grid->folder_name('目录');
            $grid->column('platform_id', '主机')->display(function($platform_id){
                return Platform::find($platform_id)->platform_name;
            });
            $grid->active_starttime('生效时间');
            $grid->active_endtime('结束时间');


            $grid->actions(function ($actions) {
                $id = $actions->getKey();
                $actions->disableEdit();
                $actions->disableDelete();
                $actions->append('<a href="access-forward/' . $id . '">转发</a>');
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


    protected function gridAccesss($platform_id)
    {
        return Admin::grid(Access::class, function (Grid $grid) use($platform_id) {

            $grid->sub_name('主体');
            $grid->folder_name('目录');
            $grid->column('platform_id', '主机')->display(function($platform_id){
                return Platform::find($platform_id)->platform_name;
            });
            $grid->active_starttime('生效时间');
            $grid->active_endtime('结束时间');


            $grid->actions(function ($actions) use($platform_id) {
                $id = $actions->getKey();
                $actions->disableEdit();
                $actions->disableDelete();
                $actions->append('<a href="/admin/access-forward/' . $id . '">转发</a>');
                $actions->append('<a href="/admin/access-whitelist-del/' . $platform_id . '/' . $id . '">&nbsp;&nbsp;删除</a>');
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
}
