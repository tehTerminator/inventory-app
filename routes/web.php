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

$router->get('/get/authenticated', ['uses' => 'UsersController@authenticate']);

$router->group(['prefix' => 'get', 'middleware' => 'auth'], function () use ($router) {
    $router->get('products', ['uses' => 'ProductController@fetch']);
    $router->get('locations', ['uses' => 'LocationController@fetch']);
});

$router->group(['prefix' => 'create', 'middleware' => 'auth'], function() use ($router) {
    $router->post('products', ['uses' => 'ProductController@create']);
    $router->post('locations', ['uses' => 'LocationController@create']);
});

$router->group(['prefix' => 'update', 'middleware' => 'auth'], function() use ($router) {
    $router->put('products', ['uses' => 'ProductController@update']);
    $router->put('locations', ['uses' => 'LocationController@update']);
});

$router->group(['prefix' => 'delete', 'middleware' => 'auth'], function() use ($router) {
    $router->delete('products', ['uses' => 'ProductController@delete']);
    $router->delete('locations', ['uses' => 'LocationController@delete']);
});
