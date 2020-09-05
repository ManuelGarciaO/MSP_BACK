<?php

/* API - TASK ACTIONS */



/* Authenticated */
Route::group(['middleware' => ['jwt.verify']], function() {
    $path = 'task';
    $controller = 'TaskController';

    //create task
    Route::post("/$path", "$controller@create");
});

