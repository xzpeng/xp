<?php

use Illuminate\Routing\Router;

Route::group([
    'prefix'        => config('admin.prefix'),
    'namespace'     => Admin::controllerNamespace(),
    'middleware'    => ['web', 'admin'],
], function (Router $router) {

    $router->get('/', 'HomeController@index');

    $router->resource('hosts', HostController::class);

    $router->get('host/{id}', 'HostController@show');


    $router->resource('strategies', StrategyController::class);

    $router->get('host-add-user/{id}', 'StrategyController@addUser');
    $router->post('host-add-users', 'StrategyController@postAddUser');
    $router->get('host-del-user/{id}', 'StrategyController@delUser');

    $router->get('host-add-process/{id}', 'StrategyController@addProcess');
    $router->post('host-add-process', 'StrategyController@postAddProcess');
    $router->get('host-del-process/{id}', 'StrategyController@delProcess');
    
    $router->get('host-add-file/{id}', 'StrategyController@addFile');
    $router->post('host-add-file', 'StrategyController@postAddFile');
    $router->get('host-del-file/{id}', 'StrategyController@delFile');


});
