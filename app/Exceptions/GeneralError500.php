<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class GeneralError500 extends Exception
{
    public function render($request, Exception $exception): JsonResponse
    {
         if ($exception instanceof UserNotFound) {
            return $exception->render($request);
        }

         return response()->json([
            'error' => 'Internal Server Error',
            'message' => 'An unexpected error occurred.',
            'details' => config('app.debug') ? $exception->getMessage() : null
        ], 500);
    }
}
