<?php

namespace App\Traits;

trait ApiResponse
{
    protected function success($data = [], $message = 'success', $code = 200)
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function error($message = 'error', $code = 400, $data = [])
    {
        return response()->json([
            'message' => $message,
            'data' => $data
        ], $code);
    }
}
