<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\ModelForm;

use App\Models\Platform;
use App\Models\Folder;
use App\Models\DirectoryTree;

use Illuminate\Http\Request;

use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Tab;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Widgets\Form;
use Encore\Admin\Tree;

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

            if ($platform->alive==1) {
                $html_add_button = '
                <div class="pull-left">
                    <span class="label label-success">主机正常</span>
                </div>
                <div class="pull-right">
                    <div class="btn-group pull-right" style="margin-right: 10px">
                        <a href="/admin/folder-whitelist-add/' . $pid . '" class="btn btn-sm btn-success">
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
            $content->row($this->gridFolders($pid));
        });
    }


    public function folderWhitelistAdd($pid, $parent_id=0, $parent_folder='/') {
        if ($parent_id==0) {
            DirectoryTree::where('platform_id', $pid)->delete();
        } else {
            $parent_folder = base64_decode($parent_folder);
        }

    	return Admin::content(function (Content $content) use($pid, $parent_id, $parent_folder) {
            $content->header('目录保护');
            $content->description('当前目录：/');
            $platform = Platform::find($pid);

            $headers = ['', '目录/文件'];
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
                
 /*               
                $xml = '<?xml version="1.0" encoding="UTF-8"?><Response><result>Success</result><message><item><file_name>/tmp/.keystone_install_lock</file_name><file_name_relative>.keystone_install_lock</file_name_relative><file_type>1</file_type></item><item><file_name>/tmp/aprfIczf9</file_name><file_name_relative>aprfIczf9</file_name_relative><file_type>1</file_type></item><item><file_name>/tmp/com.apple.launchd.1glvZv3cOU</file_name><file_name_relative>com.apple.launchd.1glvZv3cOU</file_name_relative><file_type>2</file_type></item><item><file_name>/tmp/com.apple.launchd.8XEBJ773jd</file_name><file_name_relative>com.apple.launchd.8XEBJ773jd</file_name_relative><file_type>2</file_type></item><item><file_name>/tmp/com.apple.launchd.JReff3ZINe</file_name><file_name_relative>com.apple.launchd.JReff3ZINe</file_name_relative><file_type>2</file_type></item><item><file_name>/tmp/com.apple.launchd.r0BfkWU9j4</file_name><file_name_relative>com.apple.launchd.r0BfkWU9j4</file_name_relative><file_type>2</file_type></item><item><file_name>/tmp/com.apple.launchd.Wgym89EbHN</file_name><file_name_relative>com.apple.launchd.Wgym89EbHN</file_name_relative><file_type>2</file_type></item><item><file_name>/tmp/com.apple.launchd.yC2qRjsFOh</file_name><file_name_relative>com.apple.launchd.yC2qRjsFOh</file_name_relative><file_type>2</file_type></item><item><file_name>/tmp/cvcd</file_name><file_name_relative>cvcd</file_name_relative><file_type>2</file_type></item><item><file_name>/tmp/KSOutOfProcessFetcher.CifFMeoplW</file_name><file_name_relative>KSOutOfProcessFetcher.CifFMeoplW</file_name_relative><file_type>2</file_type></item></message></Response>';
                $socket_response = new \SimpleXMLElement($xml);
*/

                if( strtolower($socket_response->result)=='success' ) {
                    $folders = $socket_response->message->item;
                    foreach ($folders as $folder) {
                        $item = array();
                        $item['parent_id'] = $parent_id;
                        $item['platform_id'] = $pid;
                        $item['order'] = 0;
                        $item['name'] = $folder->file_name;
                        $item['name_relative'] = $folder->file_name_relative;
                        $item['file_type'] = $folder->file_type;
                        DirectoryTree::create($item);
                    }
                }
            }

            $form = new Form();
            $form->action('/admin/post-add-folder');
            $form->method('post');
            $form->hidden('platform_id')->default($pid);
            $form->html(DirectoryTree::tree(function ($tree) use($pid) {
                    $tree->branch(function($branch) use($pid) {
                        if ($branch['file_type']==2) {
                            $item = '<input type="checkbox" name="folders[]" value="' . base64_encode($branch['name']) . '"/>&nbsp;&nbsp;<a href="/admin/folder-whitelist-add/' . $pid . '/' . $branch['parent_id'] . '/' . base64_encode($branch['name']) . '">' . $branch['name_relative'] . '</a>';
                        } else {
                            $item = '<input type="checkbox" name="folders[]" value="' . base64_encode($branch['name']) . '"/>&nbsp;&nbsp;' . $branch['name_relative'];
                        }
                        
                        return $item;
                    });

                    $tree->query(function ($model) use($pid) {
                        return $model->where('platform_id', $pid);
                    });
                })
            );
            $content->row( (new Box('目录保护列表', $form))->style('info')->solid() );
        });

    }


    public function folderWhitelistDel($pid, $id) {
        $folderObj = Folder::find($id);
        
        if($folderObj) {
            $platform = Platform::find($folderObj->platform_id);

            $xml_data = array(
                            'module' => 'file_manage',
                            'func' => 'del_mac_obj',
                            'info' => array(
                                'file_name' => $folderObj->folder_name,
                                'file_hash' => $folderObj->folder_hash,
                                'file_opt' => $folderObj->folder_op,
                                'group_name' => $folderObj->group_name
                            )
                        );

            $socketClient = new \App\SocketClient($platform->platform_ip, config('app.socket_remote_port'), $xml_data);
            
            $socket_response = $socketClient->send();
            $socketClient->close();

            if( strtolower($socket_response->result)=='success' ) {
                $folderObj->delete();
                return redirect('/admin/view-folder/' . $pid);
            }

            return back();
        }
    }

    public function ajax_get_sub_files($pid, $parent_id) {

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
            
            $table = new Table($headers, $rows);

            $form = new Form();
            $form->action('/admin/post-add-folder');
            $form->method('post');
            $form->hidden('platform_id')->default($pid);
            $form->html($table->render());
            $content->row(new Box('目录保护列表', $form));
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
            $grid->column('platform_id', '主机')->display(function($platform_id){
                return Platform::find($platform_id)->platform_name;
            });
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
            $grid->column('platform_id', '主机')->display(function($platform_id){
                return Platform::find($platform_id)->platform_name;
            });
            $grid->created_at('下发时间');


            $grid->actions(function ($actions) use($platform_id) {
                $id = $actions->getKey();
                $actions->disableEdit();
                $actions->disableDelete();
                $actions->append('<a href="/admin/folder-forward/' . $id . '">转发</a>');
                $actions->append('<a href="/admin/folder-whitelist-del/' . $platform_id . '/' . $id . '">&nbsp;&nbsp;删除</a>');
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
