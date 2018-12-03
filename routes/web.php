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
Route::get('disciplines', 'DisciplinesController@index');
Route::post('disciplines/save', 'DisciplinesController@save');
Route::get('disciplines/list-periods', 'DisciplinesController@listPeriods');
Route::any('disciplines/list', 'DisciplinesController@list');
Route::post('disciplines/delete', 'DisciplinesController@postDelete');
Route::get('disciplines/discipline', 'DisciplinesController@getDiscipline');
Route::post('disciplines/edit', 'DisciplinesController@postEdit');
Route::get('disciplines/ementa', 'DisciplinesController@getEmenta');

/* bind */
Route::get('bind/link', 'BindController@link');
Route::get('bind/list', 'BindController@list');

Route::get('classes', 'ClassesController@index');
Route::post('classes/classes-by-year', 'ClassesController@classesByYear');
Route::post('classes/listdisciplines', 'ClassesController@listdisciplines');
Route::get('classes/panel', 'ClassesController@getPanel');
Route::post('classes/new', 'ClassesController@postNew');
Route::get('classes/info', 'ClassesController@getInfo');
Route::post('classes/edit', 'ClassesController@postEdit');
Route::post('classes/delete', 'ClassesController@postDelete');
Route::post('classes/change-status', 'ClassesController@postChangeStatus');
Route::any('classes/list-offers', 'ClassesController@anyListOffers');
Route::post('classes/list-units/{status?}', 'ClassesController@postListUnits');
Route::post('classes/block-unit', 'ClassesController@postBlockUnit');
Route::post('classes/unblock-unit', 'ClassesController@postUnblockUnit');
Route::any('classes/create-units', 'ClassesController@anyCreateUnits');
Route::post('classes/copy-to-year', 'ClassesController@postCopyToYear');

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

Route::get('lessons', 'LessonsController@getIndex');
Route::any('lessons/new', 'LessonsController@anyNew');
Route::post('lessons/save', 'LessonsController@postSave');
Route::any('lessons/frequency', 'LessonsController@anyFrequency');
Route::post('lessons/delete', 'LessonsController@postDelete');
Route::get('lessons/info', 'LessonsController@getInfo');
Route::any('lessons/copy', 'LessonsController@anyCopy');
Route::post('lessons/list-offers', 'LessonsController@postListOffers');
Route::get('lessons/delete', 'LessonsController@anyDelete');

Route::get('sync', 'SyncController@getIndex');
Route::post('sync/receive', 'SyncController@postReceive');
Route::get('sync/receive', 'SyncController@getReceive');
Route::get('sync/error', 'SyncController@getError');

Route::get('classrooms', 'ClassroomController@getIndex');
Route::get('classrooms/campus', 'ClassroomController@getCampus');

Route::get('censo/student', 'CensoController@student');

Route::get('help/{rota}', 'HelpController@getView');

Route::get('/', 'HomeController@index');

/** ROTAS ANTIGAS

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
		Route::any('offers/get-grouped', 'OffersController@postOffersGrouped');

    Route::controller('disciplines', "DisciplinesController");
    Route::controller('lectures/units', "UnitsController");
    Route::controller('lectures', "LecturesController");
    Route::controller('avaliable', "AvaliableController");
    Route::controller('attends', "\student\DisciplinesController");
  }

  Route::controller('/', 'SocialController');
}
**/
