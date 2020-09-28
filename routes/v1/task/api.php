<?php

/* API - TASK ACTIONS */



/* Authenticated */
Route::group(['middleware' => ['jwt.verify']], function() {
    $path = 'task';
    $controller = 'TaskController';

    //create task
    Route::post("/$path", "$controller@create");

    //get 
    Route::get("/$path/{id}", "$controller@getById")->where('id','[1-9][0-9]*');

    //update task by id
    Route::patch("/$path/{id}", "$controller@update")->where('id','[1-9][0-9]*');

    //archive task by id
    Route::patch("/$path/archive/{id}", "$controller@archive")->where('id','[1-9][0-9]*');

    //searcher
    Route::get("/$path/searcher", "$controller@searcher");  

    //searcher
    Route::post("/$path/addTime", "$controller@addTime");
});

