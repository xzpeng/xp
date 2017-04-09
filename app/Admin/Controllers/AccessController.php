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

            $content->header('header');
            
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

            $content->header('header');
            $content->description('description');

            $content->body($this->grid());
        });
    }


    public function show($pid) {
        return Admin::content(function(Content $content) use($pid) {
            $content->header('header');

            $html_add_button = '<div class="pull-right">
            <div class="btn-group pull-right" style="margin-right: 10px">
                <a href="/admin/access-whitelist-add/' . $pid . '" class="btn btn-sm btn-success">
                    <i class="fa fa-save"></i>&nbsp;&nbsp;新增
                </a>
            </div>
            </div>';
            $content->row( (new Box('操作', $html_add_button))->style('info')->solid() );
            $content->row($this->gridAccesss($pid));
        });
    }


    public function accessWhitelistAdd($pid) {
        return Admin::content(function (Content $content) use($pid) {
            $content->header('目录保护');
            $content->description('/');


            $form = new Form();
            $form->action('/admin/search-accesses/' . $pid);
            $form->method('get');
            $form->text('parent_access','')->default('/');
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
                
                
                /*$xml = '<?xml version="1.0" encoding="UTF-8"?><Response><result>Success</result><message><item><file_name>/tmp/.keystone_install_lock</file_name><file_type>1</file_type></item><item><file_name>/tmp/aprfIczf9</file_name><file_type>1</file_type></item><item><file_name>/tmp/com.apple.launchd.1glvZv3cOU</file_name><file_type>2</file_type></item><item><file_name>/tmp/com.apple.launchd.8XEBJ773jd</file_name><file_type>2</file_type></item><item><file_name>/tmp/com.apple.launchd.JReff3ZINe</file_name><file_type>2</file_type></item><item><file_name>/tmp/com.apple.launchd.r0BfkWU9j4</file_name><file_type>2</file_type></item><item><file_name>/tmp/com.apple.launchd.Wgym89EbHN</file_name><file_type>2</file_type></item><item><file_name>/tmp/com.apple.launchd.yC2qRjsFOh</file_name><file_type>2</file_type></item><item><file_name>/tmp/cvcd</file_name><file_type>2</file_type></item><item><file_name>/tmp/KSOutOfProcessFetcher.CifFMeoplW</file_name><file_type>2</file_type></item></message></Response>';
                $socket_response = new \SimpleXMLElement($xml);
*/
                if( strtolower($socket_response->result)=='success' ) {
                    $accesses = $socket_response->message->item;
                    foreach ($accesses as $access) {
                        if ($access->file_type==2) {
                            $rows[] = ['<input type="checkbox" name="accesses[]" value="' . base64_encode($access->file_name) . '"/>', '<a href="/admin/search-accesses/' . $pid . '?parent_access=' . $access->file_name . '">' . $access->file_name . '</a>'];
                        } else {
                            $rows[] = ['<input type="checkbox" name="accesses[]" value="' . base64_encode($access->file_name) . '"/>', $access->file_name];
                        }
                    }
                }
            }

            $table = new Table($headers, $rows);

            $html_subs = '<div class="checkbox">
                            <label><b>选择主体：</b></label>
                            <label>
                                <input type="checkbox" name="subs[]" value="/usr/bin/vim" checked="checked"> Vim
                            </label>
                            <label>
                                <input type="checkbox" name="subs[]" value="/bin/nano" checked="checked"> Nano
                            </label>
                        </div>';

            $table2form = '<form method="POST" action="/admin/post-add-whitelist">' . $html_subs . '<hr>' . $table->render() . '<input type="hidden" name="platform_id" value="' . $pid . '" /><input type="hidden" name="_token" value="' . csrf_token() . '" /><hr><div class="btn-group pull-right"><button type="submit" class="btn btn-info pull-right">提交</button></div></form>';

            $content->row( function(Row $row) use($table2form) {
                $row->column(2,'');
                $row->column(8, (new Box('目录保护列表', $table2form))->style('info')->solid());
                $row->column(2,'');
            } );
        });

    }


    public function search($pid, Request $request) {
        $parent_access = $request->input('parent_access', '/');
        return Admin::content(function (Content $content) use($pid,$parent_access) {
            $content->header('目录保护');
            $content->description($parent_access);


            $form = new Form();
            $form->action('/admin/search-accesses/' . $pid);
            $form->method('get');
            $form->text('parent_access','')->default($parent_access);
            $content->row( (new Box('查询目录', $form))->style('info')->solid() );

            $platform = Platform::find($pid);

            $headers = ['目录/文件'];
            $rows = [];

            if ($platform) {
                
                $xml_data = array(
                            'module' => 'system_info',
                            'func' => 'query_dir',
                            'info' => array(
                                'query_path' => $parent_access
                            )
                        );

                $socketClient = new \App\SocketClient($platform->platform_ip, config('app.socket_remote_port'), $xml_data);
                $socket_response = $socketClient->send();
                $socketClient->close();

                if( strtolower($socket_response->result)=='success' ) {
                    $accesses = $socket_response->message->item;
                    foreach ($accesses as $access) {
                        if ($access->file_type==2) {
                            $rows[] = ['<input type="checkbox" name="accesses[]" value="' . base64_encode($access->file_name) . '"/>', '<a href="/admin/search-accesses/' . $pid . '?parent_access=' . $access->file_name . '">' . $access->file_name . '</a>'];
                        } else {
                            $rows[] = ['<input type="checkbox" name="accesses[]" value="' . base64_encode($access->file_name) . '"/>', $access->file_name];
                        }
                    }
                }
            }

            $table = new Table($headers, $rows);

            $html_subs = '<div class="checkbox">
                            <label><b>选择主体：</b></label>
                            <label>
                                <input type="checkbox" name="subs[]" value="/usr/bin/vim" checked="checked"> Vim
                            </label>
                            <label>
                                <input type="checkbox" name="subs[]" value="/bin/nano" checked="checked"> Nano
                            </label>
                        </div>';

            $table2form = '<form method="POST" action="/admin/post-add-whitelist">' . $html_subs . '<hr>' . $table->render() . '<input type="hidden" name="platform_id" value="' . $pid . '" /><input type="hidden" name="_token" value="' . csrf_token() . '" /><hr><div class="btn-group pull-right"><button type="submit" class="btn btn-info pull-right">提交</button></div></form>';

            $content->row( function(Row $row) use($table2form) {
                $row->column(2,'');
                $row->column(8, (new Box('目录保护列表', $table2form))->style('info')->solid());
                $row->column(2,'');
            } );
        });
    }

    public function postAddWhitelist(Request $request)
    {
        $accesses = $request->input('accesses');
        $platform_id = $request->input('platform_id');
        $subs = $request->input('subs');

        if ( $accesses && $platform_id && $subs) {

            $platform = Platform::find($platform_id);

            foreach($subs as $sub) {
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
                                        'active_starttime' => time()
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
                        $accessObj->access_name = $access;
                        $accessObj->access_hash = $access_hash;
                        $accessObj->access_op = 'read';

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
            $grid->platform_sn('序列号');


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
            $grid->access_name('目录');
            $grid->platform_id('主机');
            $grid->created_at('下发时间');


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
            $grid->access_name('目录');
            $grid->access_type('类型');
            $grid->access_op('权限');


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
}
