<?php

use Illuminate\Routing\Router;

Admin::registerHelpersRoutes();

Route::group([
    'prefix'        => config('admin.prefix'),
    'namespace'     => Admin::controllerNamespace(),
    'middleware'    => ['web', 'admin'],
], function (Router $router) {
    $router->get('/', 'CustomController@index');
    $router->resource('pins', 'PinsController');
    $router->resource('config', 'ConfigController');
    $router->get('custom/published', 'CustomController@published');
    $router->resource('custom', 'CustomController');
    $router->get('boards/sync', 'BoardsController@sync');
    $router->resource('boards', 'BoardsController');
    $router->get('chart/{id}/{type}', 'PinsController@chart');
    $router->post('download/{id}/{type}', 'PinsController@download');
});
