<?php

namespace App\Exceptions;

use Exception;

class UserNotFound extends Exception
{
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'User not found',
                'message' => $this->getMessage() ?: 'The specified user could not be found.'
            ], 404);
        }

        return response()->view('errors.404', [], 404);
    }
}
