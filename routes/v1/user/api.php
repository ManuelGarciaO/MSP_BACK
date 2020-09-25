<?php

/* API - USER ACTIONS */
$path = 'user';
$controller = 'UserController';

    //create user
    Route::post("/$path", "$controller@register");