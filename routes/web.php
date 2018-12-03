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

Route::get('student', function () {
  return view('students.disciplines');
});

Route::get('login', 'LoginController@index');
Route::post('login', 'LoginController@login');
Route::any('logout', 'LoginController@logout');

Route::get('courses', 'CoursesController@index');
Route::get('courses/edit', 'CoursesController@edit');
Route::post('courses/save', 'CoursesController@save');
Route::post('courses/all-courses', 'CoursesController@allCourses');
Route::post('courses/delete', 'CoursesController@delete');
Route::post('courses/period', 'CoursesController@period');
Route::post('courses/editperiod', 'CoursesController@editperiod');

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

// Route::controller('classes', "ClassesController");
Route::get('classes', 'ClassesController@index');
Route::post('classes/classes-by-year', 'ClassesController@classesByYear');
Route::post('classes/listdisciplines', 'ClassesController@listdisciplines');

Route::get('config', 'ConfigController@index');
Route::post('config/photo', 'ConfigController@postPhoto');
Route::post('config/birthdate', 'ConfigController@postBirthdate');
Route::post('config/common', 'ConfigController@postCommon');
Route::post('config/commonselect', 'ConfigController@postCommonselect');
Route::post('config/gender', 'ConfigController@postGender');
Route::post('config/type', 'ConfigController@postType');
Route::post('config/password', 'ConfigController@postPassword');
Route::post('config/location', 'ConfigController@postLocation');
Route::post('config/street', 'ConfigController@postStreet');
Route::post('config/uee', 'ConfigController@postUee');

// Route::controller('user', "UsersController");
Route::get('user/teacher', 'UsersController@getTeacher');
Route::get('user/student', 'UsersController@getStudent');
Route::post('user/student', 'UsersController@postStudent');
Route::any('user/find-user/{search?}', 'UsersController@anyFindUser');

Route::get('/', 'HomeController@index');

/** ROTAS ANTIGAS
Route::controller('/censo', 'CensoController');

Route::controller('/classrooms', "ClassroomController");

Route::controller('sync', "SyncController");

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
    Route::controller('disciplines', "DisciplinesController");
    Route::controller('lectures/units', "UnitsController");
    Route::controller('lectures', "LecturesController");
    Route::controller('avaliable', "AvaliableController");
    Route::controller('lessons', "LessonsController"); /* anotações de aula *
    Route::controller('attends', "\student\DisciplinesController");
		Route::any('offers/get-grouped', 'OffersController@postOffersGrouped');
  }

  Route::controller('/', 'SocialController');
}
**/
