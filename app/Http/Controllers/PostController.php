<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use App\Models\Comentario;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;


class PostController extends Controller
{
    public function Crear(Request $request) {
        $post = new Post();
    
        $post->user_id = $request->input("user_id");
        $post->titulo = $request->input("titulo");
    
        if ($request->hasFile('contenido')) {
            $file = $request->file('contenido');
            $mimeType = $file->getMimeType();
    
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/avi', 'video/mpeg'];
            if (!in_array($mimeType, $allowedMimeTypes)) {
                return response()->json(['error' => 'Solo se permiten imágenes o videos'], 400);
            }
    
            $fileName = Str::random(50) . '.' . $file->getClientOriginalExtension();
            $destinationPath = 'imagenes/posts';
            $file->move($destinationPath, $fileName);
    
            $post->contenido = $fileName;
        }
    
        if ($request->has("grupo_id")) {
            $post->grupo_id = $request->input("grupo_id");
        }
    
        $post->save();
    
        return response()->json(['mensaje' => 'Post creado correctamente', 'post' => $post]);
    }
    

    public function Listar() {
        
        $posts = Post::orderBy('created_at', 'desc')->get();
        $postsWithUserInfo = [];
    
        foreach ($posts as $post) {
            $response = Http::get("http://localhost:8000/api/usuarios/{$post->user_id}");
    
            if ($response->successful()) {
                $userInfo = $response->json();
    
                $postsWithUserInfo[] = [
                    'id' => $post->id,
                    'user_id' => $post->user_id,
                    'grupo_id' => $post->grupo_id,
                    'titulo' => $post->titulo,
                    'contenido' => $post->contenido,
                    'created_at' => Carbon::parse($post->created_at)->format('d/m/Y h:i a'),
                    'updated_at' => Carbon::parse($post->updated_at)->format('d/m/Y h:i a'),
                    'user' => [
                        'name' => $userInfo['name'],
                        'foto_perfil' => $userInfo['foto_perfil']
                    ]
                ];
            } else {
                $postsWithUserInfo[] = $post;
            }
        }
    
        return response()->json($postsWithUserInfo);
    }

    public function ListarPorUsuario($userId) {
        $posts = Post::where('user_id', $userId)->orderBy('created_at', 'desc')->get();
    
        $postsWithUserInfo = [];
    
        foreach ($posts as $post) {
            $response = Http::get("http://localhost:8000/api/usuarios/{$post->user_id}");
    
            if ($response->successful()) {
                $userInfo = $response->json();

                $postsWithUserInfo[] = [
                    'id' => $post->id,
                    'user_id' => $post->user_id,
                    'grupo_id' => $post->grupo_id,
                    'titulo' => $post->titulo,
                    'contenido' => $post->contenido,
                    'created_at' => Carbon::parse($post->created_at)->format('d/m/Y h:i a'),
                    'updated_at' => Carbon::parse($post->updated_at)->format('d/m/Y h:i a'),
                    'user' => [
                        'name' => $userInfo['name'],
                        'foto_perfil' => $userInfo['foto_perfil']
                    ]
                ];
            } else {
                $postsWithUserInfo[] = $post;
            }
        }
    
        return response()->json($postsWithUserInfo);
    }
    

    public function Eliminar($id) {
        $post = Post::find($id);

        if ($post) {
            $post->delete();
            return response()->json(['mensaje' => 'Se eliminó con éxito'], 200);
        } else {
            return response()->json(['mensaje' => 'Post no encontrado'], 404);
        }
    }

    public function Modificar(Request $request, $id) {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['mensaje' => 'Post no encontrado'], 404);
        }

        if ($request->has('titulo')) {
            $post->titulo = $request->input('titulo');
        }

        if ($request->hasFile('contenido')) {

            if ($post->contenido && file_exists(public_path('imagenes/posts/' . $post->contenido))) {
                unlink(public_path('imagenes/posts/' . $post->contenido));
            }
            
            $file = $request->file('contenido');
            $fileName = Str::random(50) . '.' . $file->getClientOriginalExtension();
            $destinationPath = 'imagenes/posts';
            $file->move($destinationPath, $fileName);

            $post->contenido = $fileName;
        } elseif ($request->has('contenido')) {
            $post->contenido = $request->input('contenido');
        }

        if ($request->has('grupo_id')) {
            $post->grupo_id = $request->input('grupo_id');
        }

        $post->save();

        return response()->json(['mensaje' => 'Post modificado con éxito', 'post' => $post], 200);
    }

    public function guardarLike(Request $request) {
        $userId = $request->user_id;
        $postId = $request->post_id;
    
        $likeExistente = Like::where('user_id', $userId)->where('post_id', $postId)->first();
    
        if ($likeExistente) {
            return response()->json(['message' => 'Ya diste like a este post'], 400);
        }
    
        $like = new Like();
        $like->user_id = $userId;
        $like->post_id = $postId;
        $like->save();
    
        return response()->json(['message' => 'Like guardado correctamente'], 201);
    }
    
    public function eliminarLike(Request $request) {
        $userId = $request->user_id;
        $postId = $request->post_id;

        $likeExistente = Like::where('user_id', $userId)->where('post_id', $postId)->first();

        if ($likeExistente) {
            $likeExistente->delete();
            return response()->json(['mensaje' => 'Like eliminado correctamente'], 200);
        } else {
            return response()->json(['mensaje' => 'Like no encontrado.'], 404);
        }
    }

    
    public function obtenerUserLikes($userId) {
        $likes = Like::where('user_id', $userId)->get(['post_id']);

        if ($likes->isEmpty()) {
            return response()->json(['likes' => []]);
        }

        return response()->json(['likes' => $likes]);
    }

    public function obtenerTodosLosLikes() {
        $likes = Like::select('post_id', DB::raw('count(*) as total_likes'))
                    ->groupBy('post_id')
                    ->get();
    
        return response()->json($likes);
    }

    public function guardarComentario(Request $request) {
        $request->validate([
            'post_id' => 'required|integer|exists:posts,id',
            'contenido' => 'required|string|max:500'
        ]);

        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token de acceso requerido'], 401);
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get('http://localhost:8000/api/validate');

        if ($response->status() != 200) {
            return response()->json(['message' => 'Token inválido o expirado'], 401);
        }

        $userId = $response->json()['id'];

        $comentario = new Comentario();
        $comentario->post_id = $request->post_id;
        $comentario->user_id = $userId;
        $comentario->contenido = $request->contenido;
        $comentario->save();

        return response()->json(['message' => 'Comentario guardado correctamente'], 201);
    }

    public function obtenerComentarios($postId) {
        $comentarios = Comentario::where('post_id', $postId)
            ->orderBy('created_at', 'desc')
            ->get();

        $comentariosWithUserInfo = [];

        foreach ($comentarios as $comentario) {
            $response = Http::get("http://localhost:8000/api/usuarios/{$comentario->user_id}");

            if ($response->successful()) {
                $userInfo = $response->json();

                $comentariosWithUserInfo[] = [
                    'id' => $comentario->id,
                    'post_id' => $comentario->post_id,
                    'user_id' => $comentario->user_id,
                    'contenido' => $comentario->contenido,
                    'created_at' => Carbon::parse($comentario->created_at)->format('d/m/Y h:i a'),
                    'updated_at' => Carbon::parse($comentario->updated_at)->format('d/m/Y h:i a'),
                    'user' => [
                        'name' => $userInfo['name'],
                        'foto_perfil' => $userInfo['foto_perfil']
                    ]
                ];
            } else {
                $comentariosWithUserInfo[] = $comentario;
            }
        }

        return response()->json($comentariosWithUserInfo);
    }

    public function obtenerComentariosCount() {
        $comentariosCount = Comentario::select('post_id', DB::raw('count(*) as comentarios_count'))
                                      ->groupBy('post_id')
                                      ->get();
    
        return response()->json($comentariosCount);
    }


}