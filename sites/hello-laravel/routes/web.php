<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PostController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/lifecycle',function(){
    return "Hello From Laravel";
});

Route::resource('posts', PostController::class);