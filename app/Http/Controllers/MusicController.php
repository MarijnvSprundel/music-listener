<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Storage;

class MusicController extends Controller
{
    private int $pos = 0;

    public function stream(Request $request): Response
    {
        $path = resource_path("mp3/folsom-prison-blues.mp3");

        if (!File::exists($path)) {
            return response()->json([
                "success" => false,
                "message" => "File not found"
            ]);
        }

        return response(File::get($path));
    }
}
