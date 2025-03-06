<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     description="Register a new user with name, email and password",
     *     operationId="register",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", format="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="name", type="array",
     *                     @OA\Items(type="string", example="The name field is required.")
     *                 ),
     *                 @OA\Property(property="email", type="array",
     *                     @OA\Items(type="string", example="The email field is required.")
     *                 ),
     *                 @OA\Property(property="password", type="array",
     *                     @OA\Items(type="string", example="The password field is required.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Password::min(8)->max(100)->letters()->numbers()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return $this->success(
            ['user' => $user],
            'User registered successfully',
            201
        );
    }

    /**
     * @OA\Post(
     * path="/api/login",
     * summary="Login user and return JWT token",
     * tags={"Authentication"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"email","password"},
     * @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     * @OA\Property(property="password", type="string", format="password", example="password123")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="JWT Token",
     * @OA\JsonContent(
     * @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     * @OA\Property(property="token_type", type="string", example="bearer"),
     * @OA\Property(property="expires_in", type="integer", example=3600)
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Invalid credentials",
     * @OA\JsonContent(
     * @OA\Property(property="error", type="string", example="Unauthorized")
     * )
     * )
     * )
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        Log::info('Login attempt:', [
            'email' => $validated['email'],
            'user_found' => $user ? true : false,
            'input_password' => $validated['password'],
            'stored_hash' => $user ? $user->password : null
        ]);

        if (!$user) {
            Log::error('Login failed: User not found for email: ' . $validated['email']);
            return $this->error('Invalid credentials', 401);
        }

        $checkResult = Hash::check($validated['password'], $user->password);
        Log::info('Password check result:', [
            'check_result' => $checkResult,
            'input_password' => $validated['password'],
            'stored_hash' => $user->password
        ]);

        if (!$checkResult) {
            Log::error('Login failed: Password mismatch for email: ' . $validated['email']);
            return $this->error('Invalid credentials', 401);
        }

        try {
            $accessToken = JWTAuth::fromUser($user);
            $refreshToken = JWTAuth::fromUser($user, ['refresh' => true]);

            // Lưu refresh token vào database
            $user->update([
                'refresh_token' => $refreshToken
            ]);

            return $this->success([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            Log::error('JWT Exception: ' . $e->getMessage());
            return $this->error('Could not create token', 500);
        }
    }

    public function refresh()
    {
        try {
            $refreshToken = request()->header('Refresh-Token');

            if (!$refreshToken) {
                return $this->error('Refresh token not provided', 401);
            }

            // Kiểm tra refresh token trong database
            $user = User::where('refresh_token', $refreshToken)->first();

            if (!$user) {
                return $this->error('Invalid refresh token', 401);
            }

            // Tạo token mới
            $accessToken = JWTAuth::fromUser($user);
            $newRefreshToken = JWTAuth::fromUser($user, ['refresh' => true]);

            // Cập nhật refresh token mới
            $user->update([
                'refresh_token' => $newRefreshToken
            ]);

            return $this->success([
                'access_token' => $accessToken,
                'refresh_token' => $newRefreshToken,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return $this->error('Token Invalid', 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return $this->error('Token Absent', 401);
        }
    }
}
