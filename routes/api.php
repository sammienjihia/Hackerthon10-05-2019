<?php

Route::group([

    'middleware' => 'api'

], function () {

    Route::post('login', 'AuthController@login');
    Route::post('register', 'AuthController@register');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');


    Route::post('createsim', 'BillingController@createSimCard');  
    Route::post('activatesim', 'BillingController@activateSimCard'); 
    Route::post('adjustbalance', 'BillingController@adjustBalance');
    Route::post('subscriberinfo', 'BillingController@subscriberInfo');

});