<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class TestController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/test",
     *     summary="Get test message",
     *     description="Returns a test message",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Hello from Swagger!"
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        return response()->json([
            'message' => 'Hello from Swagger!'
        ]);
    }
}
