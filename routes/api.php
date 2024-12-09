<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NoteController;

Route::post('/users', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::put('/user/{id}', [UserController::class, 'updateUser']);
    Route::get('/user/{id}', [UserController::class, 'getUser']);
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::delete('/user/{id}', [UserController::class, 'deleteUser']);

    //Notes
    Route::post('/notes', [NoteController::class, 'addNote']);
});
