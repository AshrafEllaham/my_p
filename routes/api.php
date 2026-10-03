<?php

use Illuminate\Support\Facades\Route;


// User (student) routes
Route::prefix('user')->group(base_path('routes/api/user.php'));

// Store routes
Route::prefix('store')->group(base_path('routes/api/store.php'));


Route::group([], function () {

    // -------------------------------------------------------
    // Public Routes (no auth required)
    // -------------------------------------------------------
    Route::group([], function () {

    });

    // -------------------------------------------------------
    // Protected Routes (Require Authentication)
    // -------------------------------------------------------
    Route::group(['middleware' => ['auth:api']], function () {

    });

    Route::group(['middleware' => ['auth:api', 'not-blocked']], function () {


        // ############################ notifications ############################


        // ############################ chat ############################

    });
});
