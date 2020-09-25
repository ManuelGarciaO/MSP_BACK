<?php

/* API - ADMIN ACTIONS */

Route::group(['middleware' => ['jwt.verify']], function() {
    $path = 'admin';
    $controller = 'AdminController';

    //create admin
    Route::post("/$path", "$controller@create");

    //searcher
    Route::get("/$path/searcher", "$controller@searcher"); 

    //disable
    Route::delete("/$path/disable/{id}", "$controller@disable"); 

    //unable
    Route::patch("/$path/unable/{id}", "$controller@unable");
}); 