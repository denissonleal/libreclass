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

// auth
Route::get('login', 'LoginController@index');
Route::post('login', 'LoginController@login');
Route::any('logout', 'LoginController@logout');

// courses
Route::get('courses', 'CoursesController@index');
Route::post('courses/save', 'CoursesController@save');

Route::get('/', 'HomeController@index');
