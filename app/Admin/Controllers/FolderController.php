<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\ModelForm;

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

    public function index(Request $request) {
    	$parent_folder = $request->input('parent_folder', '/');
    	return Admin::content(function (Content $content) use($parent_folder) {
            $content->header('目录保护');
            $content->description($parent_folder);


            $form = new Form();
    		$form->action('/admin/folders');
			$form->text('parent_folder','')->default('/home');
			$content->row( (new Box('查询目录', $form))->style('info')->solid() );


/*
            	$xml_data = array(
                            'module' => 'system_info',
                            'func' => 'folders',
                            'info' => array(
                                'dst_platform_ip' => $platform->platform_ip
                            )
                        );

	            $socketClient = new \App\SocketClient(config('app.socket_local_host'), config('app.socket_local_port'), $xml_data);
	            $socket_response = $socketClient->send();
	            $socketClient->close();
*/
            $headers = ['目录', '策略'];
            $rows = [
            		['/home/a', 'read'],
            		['/home/b', 'write|read'],
            		['/home/c', 'write'],
            	];

            $content->row( (new Box('目录保护列表', new Table($headers, $rows)))->style('info')->solid() );
        });

    }
}
