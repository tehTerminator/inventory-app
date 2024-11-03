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
    $router->get('orders/open', ['uses' => 'OrderController@fetchOpen']);
    $router->get('orders/completed', ['uses' => 'OrderController@fetchCompleted']);
    $router->get('customers', ['uses' => 'CustomerController@fetch']);
    $router->get('customer/{mobile:[0-9]{10}}', ['uses' => 'CustomerController@find']);
});

$router->group(['prefix' => 'create', 'middleware' => 'auth'], function() use ($router) {
    $router->post('products', ['uses' => 'ProductController@create']);
    $router->post('locations', ['uses' => 'LocationController@create']);
    $router->post('product/image', ['uses' => 'ProductController@uploadImage']);
    $router->post('order', ['uses' => 'OrderController@create']);
    $router->post('customer', ['uses' => 'CustomerController@create']);
});

$router->group(['prefix' => 'update', 'middleware' => 'auth'], function() use ($router) {
    $router->put('product', ['uses' => 'ProductController@update']);
    $router->put('location', ['uses' => 'LocationController@update']);
    $router->put('customer', ['uses' => 'CustomerController@update']);
    $router->put('order/update/status', ['uses' => 'OrderController@updateStatus']);
});

$router->group(['prefix' => 'delete', 'middleware' => 'auth'], function() use ($router) {
    $router->delete('products', ['uses' => 'ProductController@delete']);
    $router->delete('locations', ['uses' => 'LocationController@delete']);
});
