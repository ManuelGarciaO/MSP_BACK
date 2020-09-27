<?php

/* API - TASK ACTIONS */



/* Authenticated */
Route::group(['middleware' => ['jwt.verify']], function() {
    $path = 'stats';
    $controller = 'UserStats';

    //get 
    Route::get("/$path/getHours", "$controller@getHours");

    //get 
    Route::get("/$path/getHomeworks", "$controller@getHomeworks");

    //get 
    Route::get("/$path/getExams", "$controller@getExams");

    //get 
    Route::get("/$path/getProyects", "$controller@getProyects");
});