<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use App\Http\Controllers\GeneralController;

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

// $router->get('/', function () use ($router) {
//     return $router->app->version();
// });


$router->get('get/user/auth', ['uses' => 'UsersController@authenticate']);


$router->group(['prefix' => 'get/report', 'middleware' => 'auth'], function () use ($router) {
    $router->get('todaysIncome', ['uses' => 'VoucherController@todaysIncome']);
    $router->get('todaysExpense', ['uses' => 'VoucherController@todaysExpense']);
    $router->get('mySales', ['uses' => 'OverviewReportController@mySales']);
    $router->get('myCommission', ['uses' => 'OverviewReportController@myCommission']);
});


$router->group(['prefix' => 'get', 'middleware' => 'auth'], function () use ($router) {
    // $router->group(['prefix' => 'get'], function () use ($router) {

    $router->get('data', ['uses' => 'GeneralController@indexData']);
    $router->get('day-book', ['uses' => 'VoucherController@dayBook']);

    $router->get('balance', ['uses' => 'LedgerController@selectBalance']);

    $router->get('bundles', ['uses' => 'BundleController@select']);

    $router->get('contact', ['uses' => 'ContactController@searchByNumber']);
    $router->get('contacts', ['uses' => 'ContactController@indexContacts']);

    $router->get('general-items', ['uses' => 'GeneralController@getGeneralItems']);

    $router->get('invoice/{id:[0-9]+}', ['uses' => 'InvoiceController@getById']);

    $router->get('ledgers', ['uses' => 'LedgerController@indexLedgers']);

    $router->get('ledger-statement', ['uses' => 'VoucherController@select']);

    $router->get('location/inventory', ['uses' => 'LocationController@indexInventory']);
    $router->get('location/users', ['uses' => 'LocationController@indexUsers']);

    $router->get('products', ['uses' => 'ProductController@indexProducts']);
    $router->get('productsUsed', ['uses' => 'OverviewReportController@productsUsed']);
    $router->get('product/transferHistory', ['uses' => 'ProductController@productTransferHistory']);

    $router->get('users', ['uses' => 'UsersController@index']);
    $router->get('user/locations', ['uses' => 'UsersController@indexLocations']);

    $router->get('vouchers', ['uses' => 'VoucherController@select']);
    $router->get('vouchers/recent', ['uses' => 'VoucherController@getRecent']);
    $router->get('voucher/{id:[0-9]+}', ['uses' => 'VoucherController@getById']);

    $router->get('{table:[A-Za-z_]+}/{id:[0-9]+}', ['uses' => 'GeneralController@getById']);
    $router->get('{table:[A-Za-z_]+}', ['uses' => 'GeneralController@select']);
});




$router->group(['prefix' => 'create', 'middleware' => 'auth'], function () use ($router) {
    // $router->group(['prefix' => 'create'], function () use ($router) {
    $router->post('contact', ['uses' => 'ContactController@store']);

    $router->post('balance', ['uses' => 'LedgerController@updateBalance']);

    $router->post('bundle', ['uses' => 'BundleController@store']);
    $router->post('bundle/{id:[0-9]+}/template', ['uses' => 'BundleController@storeTemplate']);

    $router->post('invoice', ['uses' => 'InvoiceController@store']);

    $router->post('ledger', ['uses' => 'LedgerController@store']);
    $router->post('location', ['uses' => 'LocationController@store']);
    $router->post('location/user', ['uses' => 'LocationUserController@store']);

    $router->post('product', ['uses' => 'ProductController@createProduct']);
    $router->post('product-group', ['uses' => 'ProductController@createGroup']);
    $router->post('product/transfer', ['uses' => 'ProductController@transfer']);

    $router->post('user', ['uses' => 'UsersController@store']);
    $router->post('voucher', ['uses' => 'VoucherController@store']);
});

$router->group(['prefix' => 'update', 'middleware' => 'auth'], function () use ($router) {
    $router->put('balance', ['uses' => 'LedgerController@autoUpdateBalance']);
    $router->put('location', ['uses' => 'LocationController@update']);
    $router->put('ledger', ['uses' => 'LedgerController@update']);
    $router->put('contact', ['uses' => 'ContactController@update']);
    $router->put('product-group', ['uses' => 'ProductController@updateProductGroup']);
    $router->put('voucher', ['uses' => 'VoucherController@update']);
});

$router->group(['prefix' => 'destroy', 'middleware' => 'auth'], function () use ($router) {
    $router->delete('invoice/{id:[0-9]+}', ['uses' => 'InvoiceController@delete']);
});

$router->delete(
    'destroy/{table:[A-Za-z_]+}/{id:[0-9]+}',
    [
        'uses' => 'GeneralController@destroy',
        'middleware' => 'auth'
    ]
);
