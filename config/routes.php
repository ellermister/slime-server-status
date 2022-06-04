<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\HttpServer\Router\Router;

//Router::addRoute(['GET', 'POST', 'HEAD'], '/', function(\Hyperf\HttpServer\Contract\ResponseInterface $response){
//    return  "hi";
//});
//Router::get('/favicon.ico', function () {
//    return '';
//});


Router::addGroup('/api',function(){
    Router::post('/login', 'App\Controller\ManagerController@login');

        Router::addRoute(['GET', 'POST', 'HEAD'], '/test', function(\Hyperf\HttpServer\Contract\ResponseInterface $response){
            return  $response->json(["test"]);
        });
    Router::addGroup('/manager',function(){
        Router::post('/set_password', 'App\Controller\ManagerController@setPassword');
        Router::get('/nodes', 'App\Controller\ManagerController@getNodes');
        Router::get('/node/{id}', 'App\Controller\ManagerController@getNode');
        Router::post('/node/new', 'App\Controller\ManagerController@addNode');
        Router::post('/node/{id}', 'App\Controller\ManagerController@updateNode');
        Router::delete('/node/{id}', 'App\Controller\ManagerController@deleteNode');
    }, ['middleware' => [\App\Middleware\AuthMiddleware::class]]);


    Router::get('/createToken', 'App\Controller\IndexController@createToken');
    Router::get('/status', 'App\Controller\IndexController@nodeStatus');
    Router::get('/make_node_id', 'App\Controller\IndexController@createNodeId');
});

Router::addServer('ws', function () {
    Router::get('/update_status', 'App\Controller\WebSocketController');
});