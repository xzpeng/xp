<?php

use Illuminate\Routing\Router;

Route::group([
    'prefix'        => config('admin.prefix'),
    'namespace'     => Admin::controllerNamespace(),
    'middleware'    => ['web', 'admin'],
], function (Router $router) {

    $router->get('/', 'HomeController@index');

    $router->resource('platforms', PlatformController::class);

    $router->get('platform/{id}', 'PlatformController@show');


    $router->resource('strategies', StrategyController::class);
    $router->resource('softwares', SoftwareController::class);
    $router->resource('files', FileController::class);


    $router->resource('install-software', PlatformSoftwareController::class);
    $router->post('platform-software-application', 'PlatformSoftwareController@postPlatformSoftwareApplication');
    $router->get('software-install/{id}', 'PlatformSoftwareController@softwareInstall');
    $router->get('software-installing/{id}', 'PlatformSoftwareController@softwareInstalling');

    $router->resource('upload-file', PlatformFileController::class);
    $router->post('platform-file-application', 'PlatformFileController@postPlatformFileApplication');
    $router->get('file-upload/{id}', 'PlatformFileController@fileUpload');


    $router->get('platform-add-user/{id}/{msg?}', 'StrategyController@addUser');
    $router->post('platform-add-users', 'StrategyController@postAddUser');
    $router->get('platform-del-user/{id}', 'StrategyController@delUser');

    $router->get('platform-add-process/{id}/{msg?}', 'StrategyController@addProcess');
    $router->post('platform-add-process', 'StrategyController@postAddProcess');
    $router->get('platform-del-process/{id}', 'StrategyController@delProcess');

    $router->get('platform-add-file/{id}/{msg?}', 'StrategyController@addFile');
    $router->post('platform-add-file', 'StrategyController@postAddFile');
    $router->get('platform-del-file/{id}', 'StrategyController@delFile');


});
