<?php

namespace App\Http\Controllers;

use App\Http\Resources\ResponseImage;
use App\Services\Photo\PhotoService;

use Illuminate\Http\Request;
use MongoDB\Driver\Exception\ExecutionTimeoutException;

class PhotoController extends Controller
{
    public function makeImageWithWaterMark(Request $request, PhotoService $photoService)
    {
        try {
            $image = $photoService->validateRequest($request);

            if(!$image)
                throw new \Exception('Невірно обрані параметри');

            return response()->json([
                'imageInfo' => $image
            ],201);

        } catch (\Exception $exception) {
            return response()->json([
                'error' => $exception->getMessage()
            ], 422);
        }

    }

}
