<?php

/* API - USER ACTIONS */
$path = 'user';
$controller = 'UserController';

    //create user
    Route::post("/$path", "$controller@register");

    //searcher
    Route::get("/$path/searcher", "$controller@searcher");  