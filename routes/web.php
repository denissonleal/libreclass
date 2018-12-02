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

/* Erro ao detectar internet explorer */
Route::get('/ie', function () {
  return view('ie');
});

/* auth */
Route::get('login', 'LoginController@index');
Route::post('login', 'LoginController@login');
Route::any('logout', 'LoginController@logout');

/* courses */
Route::get('courses', 'CoursesController@index');
Route::post('courses/save', 'CoursesController@save');

/* period */
Route::get('periods', 'PeriodsController@index');
Route::any('periods/list', 'PeriodsController@list');
Route::any('periods/save', 'PeriodsController@save');
Route::any('periods/read', 'PeriodsController@read');

/* disciplines */
// Route::controller('disciplines', "DisciplinesController");
Route::get('disciplines', 'DisciplinesController@index');
Route::post('disciplines/save', 'DisciplinesController@save');
Route::get('disciplines/list-periods', 'DisciplinesController@listPeriods');
Route::any('disciplines/list', 'DisciplinesController@list');

/* bind */
Route::get('bind/link', 'BindController@link');
Route::get('bind/list', 'BindController@list');

Route::get('/', 'HomeController@index');

/** ROTAS ANTIGAS
Route::controller('/censo', 'CensoController');

Route::controller('/classrooms', "ClassroomController");

// Route::controller('LoL', "\student\DisciplinesController");
Route::get('student', function () {
  return View::make("students.disciplines");
});

Route::controller('sync', "SyncController");

Route::get('logout', function () {
  Session::flush();
  return Redirect::guest("/");
});

Route::get('help/{rota}', 'HelpController@getView');

if (session("user") == null) {
  Route::controller('/', 'LoginController');
} else {
  /*
   * Perfil de instituição
   *
  if (session("type") == "I") {
    Route::get('/user/scholar-report', "UsersController@printScholarReport");
    Route::post('user/teacher/delete', "UsersController@postUnlink");
    Route::post('user/teacher/update-enrollment', "UsersController@updateEnrollment");
    Route::get('classes/units/report-unit/{unit_id}', "UnitsController@getReportUnit");

    Route::post('classes/group/create', "ClassesGroupController@createMasterOffer");
    Route::post('classes/group/offers', 'ClassesGroupController@jsonOffers');
    Route::get('classes/group/{class_id}', "ClassesGroupController@loadClassGroup");

    Route::controller('courses', "CoursesController");

    Route::controller('classes/lessons', "LessonsController");
    Route::controller('classes/offers', "OffersController");
    Route::controller('classes', "ClassesController");

		Route::controller('progression', "ProgressionController");

    Route::controller('user', "UsersController");
    Route::controller('import', "CSVController");
    Route::controller('permissions', "PermissionController");
    Route::controller('lectures/units', "UnitsController");
  }
  /*
   * Perfil de professor
   *
  if (session("type") == "P") {
    Route::get('user/profile', "UsersController@getProfile");
    Route::get('user/student', "UsersController@getStudent");
    Route::post('user/student', "UsersController@postStudent");
    Route::controller('courses', "CoursesController");
    Route::controller('classes/panel', "ClassesController");
    Route::controller('classes', "ClassesController");
    Route::controller('disciplines', "DisciplinesController");
    Route::controller('lectures/units', "UnitsController");
    Route::controller('lectures', "LecturesController");
    Route::controller('avaliable', "AvaliableController");
    Route::controller('lessons', "LessonsController"); /* anotações de aula *
    Route::controller('attends', "\student\DisciplinesController");
		Route::any('offers/get-grouped', 'OffersController@postOffersGrouped');
  }

  Route::controller('config', "ConfigController");
  Route::controller('/', 'SocialController');
}
**/