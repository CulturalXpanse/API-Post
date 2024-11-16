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
Route::post('/likes/eliminar', [PostController::class, 'eliminarLike']);
Route::get('/likes/{userId}', [PostController::class, 'obtenerUserLikes']);
Route::get('/posts/likes/count', [PostController::class, 'obtenerTodosLosLikes']);
Route::post('/comentarios', [PostController::class, 'guardarComentario']);
Route::get('/posts/{postId}/comentarios', [PostController::class, 'obtenerComentarios']);
Route::get('/posts/comentarios/count', [PostController::class, 'obtenerComentariosCount']);
Route::post('/evento', [PostController::class, 'crearEvento']);
Route::get('/elementos', [PostController::class, 'ListarPostsYEventos']);
Route::get('/elementos/{userId}', [PostController::class, 'ListarPostsYEventosPorUsuario']);
Route::get('/posts-y-eventos/{userId}', [PostController::class, 'ListarPostsYEventosPorAmigosYPropios']);
Route::delete('/evento/{id}', [PostController::class, 'EliminarEvento']);
Route::get('/eventos', [PostController::class, 'listarEventos']);
Route::get('/likes/usuario/{usuarioId}', [PostController::class, 'obtenerLikesDeOtroUsuario']);
Route::get('/posts/comentarios/count/usuario/{usuarioId}', [PostController::class, 'obtenerComentariosCountDeUsuario']);
Route::get('/posts/likes/count/{postIds}', [PostController::class, 'obtenerLikesPorPostIds']);
