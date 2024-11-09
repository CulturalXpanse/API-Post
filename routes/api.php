<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

Route::post('/posts', [PostController::class, 'Crear']);
Route::get('/posts', [PostController::class, 'Listar']);
Route::get('/posts/usuario/{userId}', [PostController::class, 'ListarPorUsuario']);
Route::delete('/posts/{id}', [PostController::class, 'Eliminar']);
Route::post('/modificar/{id}', [PostController::class, 'Modificar']);
Route::post('/likes', [PostController::class, 'guardarLike']);