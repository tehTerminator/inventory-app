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
    $router->get('products', ['uses' => 'ProductsController@fetch']);
    $router->get('product', ['uses' => 'ProductsController@fetchOne']);
    $router->get('locations', ['uses' => 'LocationsController@fetchAll']);
    $router->get('location', ['uses' => 'LocationsController@fetchOne']);
    $router->get('location/orderSummary', ['uses' => 'OrderController@getOrderSummary']);
    $router->get('orders/open', ['uses' => 'OrderController@fetchOpen']);
    $router->get('orders/completed', ['uses' => 'OrderController@fetchCompleted']);
    $router->get('customers', ['uses' => 'CustomerController@fetch']);
    $router->get('customer/{mobile:[6-9]\\d{9}}', ['uses' => 'CustomerController@find']);
    $router->get('report/day-view', ['uses' => 'OrderController@dayReport']);
});

$router->group(['prefix' => 'create', 'middleware' => 'auth'], function() use ($router) {
    $router->post('product', ['uses' => 'ProductsController@create']);
    $router->post('location', ['uses' => 'LocationsController@create']);
    $router->post('product/image', ['uses' => 'ProductsController@uploadImage']);
    $router->post('orders', ['uses' => 'OrderController@create']);
    $router->post('customer', ['uses' => 'CustomerController@create']);
    $router->post('invoice', ['uses' => 'InvoiceController@create']);   
});

$router->group(['prefix' => 'update', 'middleware' => 'auth'], function() use ($router) {
    $router->put('product', ['uses' => 'ProductsController@update']);
    $router->put('location', ['uses' => 'LocationsController@update']);
    $router->put('customer', ['uses' => 'CustomerController@update']);
    $router->put('order/update/status', ['uses' => 'OrderController@updateStatus']);
});

$router->group(['prefix' => 'delete', 'middleware' => 'auth'], function() use ($router) {
    $router->delete('product', ['uses' => 'ProductsController@delete']);
    $router->delete('location', ['uses' => 'LocationsController@delete']);
});
