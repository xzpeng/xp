<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\ModelForm;

use App\Models\Platform;
use App\Models\Folder;

use Illuminate\Http\Request;

use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Tab;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Widgets\Form;

class FolderController extends Controller
{
    use ModelForm;

    public function index() {
        return Admin::content(function (Content $content) {

            $content->header('目录保护');
            
            $html_add_button = '<div class="pull-right">
            <div class="btn-group pull-right" style="margin-right: 10px">
                <a href="/admin/folder-platform-list/" class="btn btn-sm btn-success">
                    <i class="fa fa-save"></i>&nbsp;&nbsp;添加策略
                </a>
            </div>
            </div>';
            $content->row( (new Box('操作', $html_add_button))->style('info')->solid() );

            $content->row($this->gridAll());
        });
    }

    public function folderPlatformList() {
        return Admin::content(function (Content $content) {

            $content->header('目录保护');
            $content->description('主机列表');

            $content->body($this->grid());
        });
    }


    public function show($pid) {
        return Admin::content(function(Content $content) use($pid) {
            $platform = Platform::find($pid);
            $content->header('目录保护');
            $content->description('主机: ' . $platform->platform_name);

            $html_add_button = '<div class="pull-right">
            <div class="btn-group pull-right" style="margin-right: 10px">
                <a href="/admin/folder-whitelist-add/' . $pid . '" class="btn btn-sm btn-success">
                    <i class="fa fa-save"></i>&nbsp;&nbsp;新增
                </a>
            </div>
            </div>';
            $content->row( (new Box('操作', $html_add_button))->style('info')->solid() );
            $content->row($this->gridFolders($pid));
        });
    }


    public function folderWhitelistAdd($pid) {
    	return Admin::content(function (Content $content) use($pid) {
            $content->header('目录保护');
            $content->description('当前目录：/');


            $form = new Form();
            $form->action('/admin/search-folders/' . $pid);
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
                $socket_response = new \SimpleXMLElement($xml);*/

                if( strtolower($socket_response->result)=='success' ) {
                    $folders = $socket_response->message->item;
                    foreach ($folders as $folder) {
                        if ($folder->file_type==2) {
                            $rows[] = ['<input type="checkbox" name="folders[]" value="' . base64_encode($folder->file_name) . '"/>', '<a href="/admin/search-folders/' . $pid . '?parent_folder=' . $folder->file_name . '">' . $folder->file_name . '</a>'];
                        } else {
                            $rows[] = ['<input type="checkbox" name="folders[]" value="' . base64_encode($folder->file_name) . '"/>', $folder->file_name];
                        }
                    }
                }
            }
            $table = new Table($headers, $rows);
            $table2form = '<form method="POST" action="/admin/post-add-whitelist">' . $table->render() . '<input type="hidden" name="platform_id" value="' . $pid . '" /><input type="hidden" name="_token" value="' . csrf_token() . '" /><hr><div class="btn-group pull-right"><button type="submit" class="btn btn-info pull-right">提交</button></div></form>';

            $content->row( function(Row $row) use($table2form) {
                $row->column(2,'');
                $row->column(8, (new Box('目录保护列表', $table2form))->style('info')->solid());
                $row->column(2,'');
            } );
        });

    }


    public function search($pid, Request $request) {
        $parent_folder = $request->input('parent_folder', '/');
        return Admin::content(function (Content $content) use($pid,$parent_folder) {
            $content->header('目录保护');
            $content->description('当前目录：' . $parent_folder);


            $form = new Form();
            $form->action('/admin/search-folders/' . $pid);
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
                    $folders = $socket_response->message->item;
                    foreach ($folders as $folder) {
                        if ($folder->file_type==2) {
                            $rows[] = ['<input type="checkbox" name="folders[]" value="' . base64_encode($folder->file_name) . '"/>', '<a href="/admin/search-folders/' . $pid . '?parent_folder=' . $folder->file_name . '">' . $folder->file_name . '</a>'];
                        } else {
                            $rows[] = ['<input type="checkbox" name="folders[]" value="' . base64_encode($folder->file_name) . '"/>', $folder->file_name];
                        }
                    }
                }
            }
            $table = new Table($headers, $rows);
            $table2form = '<form method="POST" action="/admin/post-add-whitelist">' . $table->render() . '<input type="hidden" name="platform_id" value="' . $pid . '" /><input type="hidden" name="_token" value="' . csrf_token() . '" /><hr><div class="btn-group pull-right"><button type="submit" class="btn btn-info pull-right">提交</button></div></form>';
            $content->row( function(Row $row) use($table2form) {
                $row->column(2,'');
                $row->column(8, (new Box('目录保护列表', $table2form))->style('info')->solid());
                $row->column(2,'');
            } );
        });
    }

    public function postAddWhitelist(Request $request)
    {
        $folders = $request->input('folders');
        $platform_id = $request->input('platform_id');

        if ( $folders && $platform_id) {

            $platform = Platform::find($platform_id);
            $data_folder = array();

            foreach ($folders as $folder) {

                $folder = base64_decode($folder);
                $folder_hash = hash('md5', $folder);
                $xml_data = array(
                                'module' => 'file_manage',
                                'func' => 'add_mac_obj',
                                'info' => array(
                                    'file_name' => $folder,
                                    'file_size' => 0,
                                    'file_hash' => $folder_hash,
                                    'file_opt' => 'read',
                                    'group_name' => '',
                                    'active_starttime' => time()
                                )
                            );

                $socketClient = new \App\SocketClient($platform->platform_ip, config('app.socket_remote_port'), $xml_data);
                
                $socket_response = $socketClient->send();
                $socketClient->close();

                if( strtolower($socket_response->result)=='success' ) {
                    $folderObj = new Folder;
                    $folderObj->platform_id = $platform_id;
                    $folderObj->folder_name = $folder;
                    $folderObj->folder_hash = $folder_hash;
                    $folderObj->folder_op = 'read';

                    $folderObj->save();
                }
            }
            return redirect('/admin/view-folder/' . $platform_id);
        } else {
            return redirect('/admin/view-folder/' . $platform_id);
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
                $actions->append('<a href="view-folder/' . $id . '">查看</a>');
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
        return Admin::grid(Folder::class, function (Grid $grid) {
            $grid->folder_name('目录');
            $grid->platform_id('主机');
            $grid->created_at('下发时间');


            $grid->actions(function ($actions) {
                $id = $actions->getKey();
                $actions->disableEdit();
                $actions->disableDelete();
                $actions->append('<a href="folder-forward/' . $id . '">转发</a>');
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


    protected function gridFolders($platform_id)
    {
        return Admin::grid(Folder::class, function (Grid $grid) use($platform_id) {

            $grid->folder_name('目录');
            $grid->platform_id('主机');
            $grid->created_at('下发时间');


            $grid->actions(function ($actions) {
                $id = $actions->getKey();
                $actions->disableEdit();
                $actions->disableDelete();
                $actions->append('<a href="view-folder/' . $id . '">查看</a>');
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
