<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Http\Request;


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
}