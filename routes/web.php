<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});


$router->get('/user/auth', ['uses' => 'UsersController@authenticate']);

$router->group(['prefix'=>'get', 'middleware' => 'auth'], function() use ($router) {
    $router->get('locations/', ['uses' => 'LocationController@index']);
    $router->get('location/{$id:[0-9]+}', ['uses' => 'LocationController@find']);
    $router->get('locations/{$name:[A-Za-z]+}', ['uses' => 'LocationController@findByName']);
});

$router->group(['prefix' => 'create', 'middleware' => 'auth'], function() use ($router) {
    $router->put('location', ['uses' => 'LocationController@store']);
});

$router->group(['prefix' => 'update', 'middleware' => 'auth'], function() use ($router) {
    $router->put('location/{$id:[0-9]+}', ['uses' => 'LocationController@destroy']);
})