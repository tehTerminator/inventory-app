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

$router->group(['prefix' => 'get', 'middleware' => 'auth'], function () use ($router) {
// $router->group(['prefix' => 'get'], function () use ($router) {
    $router->get('customers', ['uses' => 'ContactController@indexCustomer']);
    $router->get('suppliers', ['uses' => 'ContactController@indexSupplier']);
    $router->get('locations', ['uses' => 'LocationController@index']);

    $router->get('product-groups', ['uses' => 'ProductController@indexGroups']);
    $router->get('products', ['uses' => 'ProductController@indexProducts']);
    
    $router->get('product-group/{id:[0-9]+}', ['uses' => 'ProductController@getGroupById']);
    $router->get('location/{id:[0-9]+}', ['uses' => 'LocationController@find']);
    $router->get('locations/{name:[A-Za-z]+}', ['uses' => 'LocationController@findByName']);
    $router->get('customer/{id:[0-9]+}', ['uses' => 'ContactController@show']);
    $router->get('supplier/{id:[0-9]+}', ['uses' => 'ContactController@show']);
    $router->get('product/{id:[0-9]+}', ['uses' => 'ProductController@getProductById']);
    $router->get('products/{title:[a-zA-Z]+}', ['uses' => 'ProductController@getProductsByTitle']);
});

$router->group(['prefix' => 'create', 'middleware' => 'auth'], function () use ($router) {
    $router->post('location', ['uses' => 'LocationController@store']);
    $router->post('contact', ['uses' => 'ContactController@store']);

    // $router->post('contact', function() {
    //     return response('create/contact');
    // });

    $router->post('product-group', ['uses' => 'ProductController@createGroup']);
    $router->post('product', ['uses' => 'ProductController@createProduct']);
});

$router->group(['prefix' => 'update', 'middleware' => 'auth'], function () use ($router) {
    $router->put('location', ['uses' => 'LocationController@update']);
    $router->put('contact', ['uses' => 'ContactController@update']);
    $router->put('product-group', ['uses' => 'ProductController@updateProductGroup']);
});

$router->group(['prefix' => 'destroy', 'middleware' => 'auth'], function () use ($router) {
    $router->delete('location/{id:[0-9]+}', ['uses' => 'LocationController@destroy']);
    $router->delete('contact/{id:[0-9]+}', ['uses' => 'ContactController@destroy']);
});
