<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth0/callback', '\Auth0\Login\Auth0Controller@callback');


Route::post('/gitlab', 'DefaultController@index');

Route::get('/login', 'ReportsController@login');