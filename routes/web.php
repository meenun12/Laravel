<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/fundings', 'FundingsController@index');

Route::get('/investors', 'InvestorsController@index');

Route::get('/companies', 'CompanyController@index');

Route::get('/employees_chart', 'CompanyController@employees_chart');


