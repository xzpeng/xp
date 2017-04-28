<?php

use Illuminate\Routing\Router;

Route::group([
    'prefix'        => config('admin.prefix'),
    'namespace'     => Admin::controllerNamespace(),
    'middleware'    => ['web', 'admin'],
], function (Router $router) {

    $router->get('/', 'HomeController@index');

    $router->resource('platforms', PlatformController::class);


    $router->resource('strategies', StrategyController::class);
    $router->resource('securitysoft-packages', SecuritysoftController::class);
    $router->resource('kexin-packages', KexinPackageController::class);
    $router->resource('whitelists', WhitelistController::class);
    $router->resource('dt', DirectoryTreeController::class);

    // 目录保护
    $router->get('folders', 'FolderController@index');
    $router->get('view-folder/{pid}', 'FolderController@show');
    $router->get('folder-whitelist-add/{pid}', 'FolderController@folderWhitelistAdd');
    $router->get('folder-whitelist-del/{pid}/{id}', 'FolderController@folderWhitelistDel');
    $router->get('search-folders/{pid}', 'FolderController@search');
    $router->post('post-add-folder', 'FolderController@postAddWhitelist');
    $router->get('folder-platform-list', 'FolderController@folderPlatformList');

    // 访问控制
    $router->get('accesses', 'AccessController@index');
    $router->get('view-access/{pid}', 'AccessController@show');
    $router->get('access-whitelist-add/{pid}', 'AccessController@accessWhitelistAdd');
    $router->get('access-whitelist-del/{pid}/{id}', 'AccessController@accessWhitelistDel');
    $router->get('search-accesses/{pid}', 'AccessController@search');
    $router->post('post-add-access', 'AccessController@postAddWhitelist');
    $router->get('access-platform-list', 'AccessController@accessPlatformList');


    $router->resource('install-securitysoft', PlatformSecuritysoftController::class);
    $router->get('distribution-kexin-package/{id}', 'PlatformKexinPackageController@distribution');
    $router->post('distributie-kexin-package', 'PlatformKexinPackageController@postDistribution');
    $router->get('distribution-records/{id}', 'PlatformKexinPackageController@records');

    $router->get('platform/{id}', 'PlatformController@show');
    $router->get('kexin-package/{id}', 'KexinPackageController@show');



    $router->post('platform-securitysoft-application', 'PlatformSecuritysoftController@postPlatformSecuritysoftApplication');
    $router->get('securitysoft-install/{id}', 'PlatformSecuritysoftController@securitysoftInstall');
    $router->get('securitysoft-installing/{id}', 'PlatformSecuritysoftController@securitysoftInstalling');

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
