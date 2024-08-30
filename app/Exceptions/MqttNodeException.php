<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class MqttNodeException extends Exception
{
    /**
     * Additional data related to the exception.
     *
     * @var array
     */
    protected $data;

    /**
     * Create a new exception instance.
     *
     * @param  string  $message
     * @param  array  $data
     * @return void
     */
    public function __construct($message = "", array $data = [])
    {
        parent::__construct($message);
        $this->data = $data;
    }

    /**
     * Get the additional data related to the exception.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $this->getMessage(),
                'data' => $this->getData(),
            ], 400);
        }

        return parent::render($request);
    }
}
