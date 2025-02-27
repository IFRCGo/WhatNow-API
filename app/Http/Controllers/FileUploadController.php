<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(
 *     name="Files",
 *     description="Operations about Regions"
 * )
 */
class FileUploadController extends Controller
{
    /**
     * @OA\Post(
     *     path="/upload",
     *     tags={"Whatnow"},
     *     summary="Upload a file",
     *     operationId="uploadFile",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="File to upload (allowed types: jpg, png; max size: 10MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
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
