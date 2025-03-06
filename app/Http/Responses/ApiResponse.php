<?php

// app/Http/Responses/ApiResponse.php

namespace App\Http\Responses;

class ApiResponse
{
    public static function success($data = null, $message = 'Success', $code = 200)
    {
        return response()->json([
            'status' => $code,
            'data' => $data,
            'message' => $message,
        ],  $code);
    }

    public static function error($data = null, $message = 'Error', $code = 400)
    {
        return response()->json([
            'code' => $code,
            'data' => $data,
            'message' => $message,
        ], $code);
    }
}
