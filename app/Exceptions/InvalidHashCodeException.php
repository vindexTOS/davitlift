<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class InvalidHashCodeException extends Exception
{
    protected $expectedHash;
    protected $actualHash;

    public function __construct(string $expectedHash, string $actualHash)
    {
        parent::__construct("Invalid hash code");
        $this->expectedHash = $expectedHash;
        $this->actualHash = $actualHash;
    }

    public function render(): JsonResponse
    {
        return response()->json(
            [
                'code' => 3,
                'message' => $this->getMessage(),
                'expected' => strtoupper($this->expectedHash),
                'actual' => strtoupper($this->actualHash),
            ],
            JsonResponse::HTTP_BAD_REQUEST
        );
    }
}