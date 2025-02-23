<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        try {

            $request->validate([
                'file' => 'required|file|mimes:jpg,png|max:10240',
            ]);


            $file = $request->file('file');


            $fileName = $file->getClientOriginalName();


            $path = Storage::disk('azure')->putFileAs('', $file, $fileName);

            return response()->json(['path' => $path], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }
}
