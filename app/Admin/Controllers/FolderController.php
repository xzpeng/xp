<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\ModelForm;

use App\Models\Platform;

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

            $content->header('header');
            $content->description('description');

            $content->body($this->grid());
        });
    }

    public function show($pid) {
    	return Admin::content(function (Content $content) use($pid) {
            $content->header('目录保护');
            $content->description('/');


            $form = new Form();
            $form->action('/admin/search-folders/' . $pid);
    		$form->method('get');
			$form->text('parent_folder','')->default('/');
			$content->row( (new Box('查询目录', $form))->style('info')->solid() );

            $platform = Platform::find($pid);

            $headers = ['目录/文件'];
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
                $socketClient->sendHeader();
                $socket_response = $socketClient->send();
                $socketClient->close();

                $response = simplexml_load_string($socket_response);
                if( strtolower($response->result)=='success' ) {
                    $folders = $response->message->item;
                    foreach ($folders as $folder) {
                        if ($folder->file_type==2) {
                            $rows[][] = '<a href="/admin/search-folders/' . $pid . '?parent_folder=' . $folder->file_name . '">' . $folder->file_name . '</a>';
                        } else {
                            $rows[][] = $folder->file_name;
                        }
                    }
                }
            }

            $content->row( (new Box('目录保护列表', new Table($headers, $rows)))->style('info')->solid() );
        });

    }


    public function search($pid, Request $request) {
        $parent_folder = $request->input('parent_folder', '/');
        return Admin::content(function (Content $content) use($pid,$parent_folder) {
            $content->header('目录保护');
            $content->description($parent_folder);


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
                                'query_path' => '/'
                            )
                        );

                $socketClient = new \App\SocketClient($platform->platform_ip, config('app.socket_remote_port'), $xml_data);
                $socketClient->sendHeader();
                $socket_response = $socketClient->send();
                $socketClient->close();

                $response = simplexml_load_string($socket_response);
                if( strtolower($response->result)=='success' ) {
                    $folders = $response->message->item;
                    foreach ($folders as $folder) {
                        if ($folder->file_type==2) {
                            $rows[][] = '<a href="/admin/search-folders/' . $pid . '?parent_folder=' . $folder->file_name . '">' . $folder->file_name . '</a>';
                        } else {
                            $rows[][] = $folder->file_name;
                        }
                    }
                }
            }

            $content->row( (new Box('目录保护列表', new Table($headers, $rows)))->style('info')->solid() );
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

            //$grid->id('ID')->sortable();

            $grid->platform_name('主机名');
            $grid->platform_ip('IP地址');
            $grid->platform_sn('序列号');


            $grid->actions(function ($actions) {
                $id = $actions->getKey();
                $actions->disableEdit();
                $actions->disableDelete();
                $actions->append('<a href="view-folder/' . $id . '">查看</a>');
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
}
