<?php

namespace App\Http;

trait Helper
{
    public $httpCode = [
        'StatusOK' => 200,
        'StatusCreated' => 201,
        'StatusBadRequest' => 400,
        'StatusUnauthorized' => 401,
        'StatusMethodNotAllowed' => 405,
        'StatusUnprocessableEntity' => 422,
        'StatusInternalServerError' => 500,
        'StatusServiceUnavailable' => 503,
        'StatusGatewayTimeout' => 504
    ];

    public $httpMessage = [
        'StatusOK' => 'OK',
        'StatusCreated' => 'Created',
        'StatusBadRequest' => 'Bad Request',
        'StatusUnauthorized' => 'Unauthorized',
        'StatusMethodNotAllowed' => 'Method Not Allowed',
        'StatusUnprocessableEntity' => 'Unprocessable Entity',
        'StatusInternalServerError' => 'Internal Server Error',
        'StatusServiceUnavailable' => 'Service Unvailable',
        'StatusGatewayTimeout' => 'Gateway Timeout'
    ];

    public function responseFormatter($statuscode, $message, $data = NULL)
    {
        $data = [
            'code' => $statuscode,
            'data' => $data,
            'message' => $message,
        ];
        return response()->json($data, $statuscode);
    }

    public function responseFormatterWithMeta($statuscode, $message, $data = NULL)
    {
        $data = [
            'code' => $statuscode,
            'data' => $data->items(),
            'meta' => [
                'next_page' => ($data->nextCursor() != null) ? $data->nextCursor()->encode() : null,
                'prev_page' => ($data->previousCursor() != null) ? $data->previousCursor()->encode() : null,
            ],
            'message' => $message,
        ];
        return response()->json($data, $statuscode);
    }

    public function errorResponseFormatter($statuscode, $message, $errors = NULL)
    {
        $data = [
            'code' => $statuscode,
            'errors' => $errors,
            'message' => $message,
        ];
        return response()->json($data, $statuscode);
    }
}
