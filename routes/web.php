<?php

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

/* attends */
Route::get('attends', 'Student\DisciplinesController@getIndex');
Route::get('attends/units/{offer}', 'Student\DisciplinesController@getUnits');
Route::post('attends/resume-unit/{unit}', 'Student\DisciplinesController@postResumeUnit');

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

/* usuário */
Route::get('user/teacher', 'UsersController@getTeacher');
Route::get('user/student', 'UsersController@getStudent');
Route::post('user/student', 'UsersController@postStudent');
Route::any('user/find-user/{search?}', 'UsersController@anyFindUser');
Route::get('/user/scholar-report', 'UsersController@printScholarReport');
Route::post('user/teacher/delete', 'UsersController@postUnlink');
Route::post('user/teacher/update-enrollment', 'UsersController@updateEnrollment');
Route::post('user/search-teacher', 'UsersController@postSearchTeacher');
Route::any('user/teacher-friends', 'UsersController@anyTeachersFriends');
Route::post('user/teacher', 'UsersController@postTeacher');
Route::get('user/profile-student', 'UsersController@getProfileStudent');
Route::post('user/get-student', 'UsersController@postGetStudent');
Route::any('user/reporter-student-class', 'UsersController@anyReporterStudentClass');
Route::get('user/reporter-student-offer', 'UsersController@getReporterStudentOffer');
Route::post('user/profile-student', 'UsersController@postProfileStudent');
Route::post('user/attest', 'UsersController@postAttest');
Route::get('user/profile-teacher', 'UsersController@getProfileTeacher');
Route::post('user/invite', 'UsersController@postInvite');
Route::get('user/infouser', 'UsersController@getInfouser');
Route::any('user/link', 'UsersController@anyLink');

/* lesson */
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

/* units */
Route::get('lectures/units', 'UnitsController@getIndex');
Route::post('lectures/units/edit', 'UnitsController@postEdit');
Route::get('lectures/units/new', 'UnitsController@getNew');
Route::get('lectures/units/stude', 'UnitsController@getStudent');
Route::post('lectures/units/rmstudent', 'UnitsController@postRmstudent');
Route::post('lectures/units/addstudent', 'UnitsController@postAddstudent');
Route::get('lectures/units/newunit', 'UnitsController@getNewunit');
Route::get('lectures/units/reportunitz', 'UnitsController@getReportunitz');
Route::get('lectures/units/report-unit', 'UnitsController@getReportUnit');
Route::get('classes/units/report-unit/{unit_id}', 'UnitsController@getReportUnit');

/* classes */
Route::post('classes/group/create', 'ClassesGroupController@createMasterOffer');
Route::post('classes/group/offers', 'ClassesGroupController@jsonOffers');
Route::get('classes/group/{class_id}', 'ClassesGroupController@loadClassGroup');


Route::get('import', 'CSVController@getIndex');
Route::post('import', 'CSVController@postIndex');
Route::get('import/confirm-classes', 'CSVController@getConfirmClasses');
Route::get('import/confirmattends', 'CSVController@getConfirmattends');
Route::post('import/classwithteacher', 'CSVController@postClasswithteacher');
Route::get('import/teacher', 'CSVController@getTeacher');
Route::get('import/offer', 'CSVController@getOffer');
Route::get('import/confirmoffer', 'CSVController@getConfirmoffer');

Route::any('offers/get-grouped', 'OffersController@postOffersGrouped');
Route::get('classes/offers', 'OffersController@getIndex');
Route::get('classes/offers/user', 'OffersController@getUser');
Route::get('classes/offers/unit', 'OffersController@getUnit');
Route::post('classes/offers/teacher', 'OffersController@postTeacher');
Route::post('classes/offers/status', 'OffersController@postStatus');
Route::get('classes/offers/student', 'OffersController@getStudents');
Route::post('classes/offers/status-student', 'OffersController@postStatusStudent');
Route::any('classes/offers/delete-last-unit', 'OffersController@anyDeleteLastUnit');
Route::post('classes/offers/offers-grouped', 'OffersController@postOffersGrouped');

Route::post('progression/students-and-classes', 'ProgressionController@postStudentsAndClasses');
Route::post('progression/import-student', 'ProgressionController@postImportStudent');

Route::get('permissions', 'PermissionController@getIndex');
Route::post('permissions', 'PermissionController@postIndex');
Route::post('permissions/find', 'PermissionController@postFind');

Route::get('lectures', 'LecturesController@getIndex');
Route::get('lectures/finalreport/{offer?}', 'LecturesController@getFinalreport');
Route::get('lectures/frequency/{offer}', 'LecturesController@getFrequency');
Route::post('lectures/sort', 'LecturesController@postSort');

Route::get('avaliable', 'AvaliableController@getIndex');
Route::get('avaliable/new', 'AvaliableController@getNew');
Route::get('avaliable/finaldiscipline', 'AvaliableController@getFinaldiscipline');
Route::get('avaliable/average-unit', 'AvaliableController@getAverageUnit');
Route::get('avaliable/liststudentsexam/{exam}', 'AvaliableController@getListstudentsexam');
Route::get('avaliable/finalunit', 'AvaliableController@getFinalunit');
Route::post('avaliable/save', 'AvaliableController@postSave');
Route::post('avaliable/exam', 'AvaliableController@postExam');
Route::post('avaliable/exam-descriptive', 'AvaliableController@postExamDescriptive');
Route::post('avaliable/finalunit', 'AvaliableController@postFinalunit');
Route::post('avaliable/finaldiscipline', 'AvaliableController@postFinaldiscipline');
Route::post('avaliable/offer', 'AvaliableController@postOffer');
Route::post('avaliable/delete', 'AvaliableController@postDelete');

Route::get('censo/student', 'CensoController@student');

Route::get('help/{rota}', 'HelpController@getView');

Route::post('question', 'SocialController@postQuestion');
Route::post('suggestion', 'SocialController@postSuggestion');

Route::get('ie', 'HomeController@ie');
Route::get('student', 'HomeController@student');
Route::get('/', 'HomeController@index');
