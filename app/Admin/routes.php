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
    $router->get('host-del-user/{id}', 'StrategyController@delUser');
    $router->get('host-add-process/{id}', 'StrategyController@addProcess');
    $router->get('host-del-process/{id}', 'StrategyController@delProcess');
    $router->get('host-add-file/{id}', 'StrategyController@addFile');
    $router->get('host-del-file/{id}', 'StrategyController@delFile');


});
