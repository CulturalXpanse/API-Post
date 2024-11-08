<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;


class PostController extends Controller
{
    public function Crear(Request $request){
        $post = new Post();

        $post->user_id = $request->input("user_id");
        $post->titulo = $request->input("titulo");

        if ($request->hasFile('contenido')) {
            $file = $request->file('contenido');
            $fileName = Str::random(50) . '.' . $file->getClientOriginalExtension();
            $destinationPath = 'imagenes/posts';
            $file->move($destinationPath, $fileName);

            $post->contenido = $fileName;
        }
        if ($request->has("grupo_id")) {
            $post->grupo_id = $request->input("grupo_id");
        }

        $post -> save();

        return response()->json([ 'mensaje' => 'Post creado correctamente', 'post' => $post ]);
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
                    'created_at' => Carbon::parse($post->created_at)->translatedFormat('j \d\e F \a \l\a\s h:i a'),
                    'updated_at' => Carbon::parse($post->created_at)->translatedFormat('j \d\e F \a \l\a\s h:i a'),
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
                    'created_at' => Carbon::parse($post->created_at)->translatedFormat('j \d\e F \a \l\a\s h:i a'),
                    'updated_at' => Carbon::parse($post->created_at)->translatedFormat('j \d\e F \a \l\a\s h:i a'),
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
}