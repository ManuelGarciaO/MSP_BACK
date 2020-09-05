<?php

/* API - SESSION ACTIONS */
$path = 'session';
$controller = 'SessionController';




//create login (create session token)
Route::post("/$path", "$controller@login");//->middleware('throttle:60,1');


//password-code (send a code to email)
Route::post("/$path/mail", "$controller@sendEmailCode");

//get a authenticated token (exchange code)
Route::post("/$path/mail/code", "$controller@exchangeEmailCode");



/* Authenticated */
Route::group(['middleware' => ['jwt.verify']], function() {
    $path = 'session';
    $controller = 'SessionController';

    //refresh session token
    Route::patch("/$path/refresh", "$controller@refresh_session");

    //revoke session token
    Route::delete("/$path", "$controller@revoke_session");

    //get info session
    Route::get("/$path", "$controller@getInfo");
});
