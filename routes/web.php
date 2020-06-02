<?php

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
$router->group(['prefix' => 'ticket_approve','middleware' => 'App\Http\Middleware\Oauth2'], function () use ($router) {
    $router->get('lists', ['as' => 'ticket_approve_lists', 'uses' => 'TicketApproveController@lists']);
    $router->get('detail', ['as' => 'ticket_approve_detail', 'uses' => 'TicketApproveController@detail']);
    $router->post('accept', ['as' => 'ticket_approve_accept', 'uses' => 'TicketApproveController@accept']);
    $router->post('reject', ['as' => 'ticket_approve_reject', 'uses' => 'TicketApproveController@reject']);
});
$router->group(['prefix' => 'ticket','middleware' => 'App\Http\Middleware\Oauth2'], function () use ($router) {
    $router->post('delete', ['as' => 'ticket_delete', 'uses' => 'TicketController@delete']);
    $router->get('lists', ['as' => 'ticket_list', 'uses' => 'TicketController@lists']);
    $router->get('detail', ['as' => 'ticket_detail', 'uses' => 'TicketController@detail']);
    $router->post('add', ['as' => 'ticket_add', 'uses' => 'TicketController@add']); 
});
$router->group(['prefix' => 'ticket_type','middleware' => 'App\Http\Middleware\Oauth2'], function () use ($router) {
    $router->get('lists', ['as' => 'ticket_type_lists', 'uses' => 'TicketTypeController@lists']);
    $router->get('detail', ['as' => 'ticket_type_detail', 'uses' => 'TicketTypeController@detail']);
});