<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;

/**
 * @OA\Info(title="API Documentation", version="1.0.0")
 *
 * @OA\Tag(
 *     name="Posts",
 *     description="API để quản lý bài viết"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Post",
 *     type="object",
 *     title="Post",
 *     @OA\Property(property="id", type="integer", format="int64", description="ID của bài viết", example=1),
 *     @OA\Property(property="title", type="string", description="Tiêu đề bài viết", example="Tiêu đề bài viết mẫu"),
 *     @OA\Property(property="content", type="string", description="Nội dung bài viết", example="Nội dung bài viết mẫu"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Thời gian tạo", example="2025-03-10T08:03:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Thời gian cập nhật", example="2025-03-10T08:03:00Z"),
 * )
 */
class PostController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/posts",
     *     summary="Lấy danh sách bài viết",
     *     tags={"Posts"},
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách bài viết",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Post")),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server",
     *     )
     * )
     */
    public function index()
    {
        $posts = Post::all();
        return ApiResponse::success($posts, 'Lấy danh sách bài viết thành công');
    }

    public function create(Request $request) {}


    /**
     * @OA\Post(
     *     path="/api/posts",
     *     summary="Tạo bài viết mới",
     *     tags={"Posts"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Bài viết được tạo thành công",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu không hợp lệ",
     *     )
     * )
     */
    public function store(Request $request)
    {
        $requestData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $userId = $request->user()->id;

        $post = Post::create([
            'title' => $requestData['title'],
            'content' => $requestData['content'],
            'user_id' => $userId,
        ]);

        return ApiResponse::success($post, 'Bài viết đã được tạo thành công');
    }

    /**
     * @OA\Get(
     *     path="/api/posts/{id}",
     *     summary="Lấy thông tin bài viết",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin bài viết",
     *         @OA\JsonContent(ref="#/components/schemas/Post"),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bài viết không tìm thấy",
     *     )
     * )
     */
    public function show(string $id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'Bài viết không tìm thấy'], 404);
        }
        return response()->json($post);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * @OA\Put(
     *     path="/api/posts/{id}",
     *     summary="Cập nhật bài viết",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", example="Tiêu đề bài viết cập nhật"),
     *             @OA\Property(property="content", type="string", example="Nội dung bài viết cập nhật")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bài viết được cập nhật thành công",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bài viết không tìm thấy",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dữ liệu không hợp lệ",
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $post = Post::find($id);
        if (!$post) {
            return ApiResponse::error('Bài viết không tìm thấy', 404);
        }

        $requestData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $post->update([
            'title' => $requestData['title'],
            'content' => $requestData['content'],
        ]);

        return ApiResponse::success(null, 'Bài viết đã được cập nhật thành công');
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     path="/api/posts/{id}",
     *     summary="Xóa bài viết",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bài viết đã được xóa thành công",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bài viết không tìm thấy",
     *     )
     * )
     */
    public function destroy(string $id)
    {
        // Tìm bài viết theo ID
        $post = Post::find($id);
        if (!$post) {
            return ApiResponse::error('Bài viết không tìm thấy', 404);
        }

        // Xóa bài viết
        $post->delete();

        // Trả về thông báo thành công
        return ApiResponse::success(null, 'Bài viết đã xoá nhật thành công');
    }

    /**
     * @OA\Get(
     *     path="/api/posts/find/{id}",
     *     summary="Tìm bài viết theo ID",
     *     tags={"Posts"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thông tin bài viết",
     *         @OA\JsonContent(ref="#/components/schemas/Post"),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bài viết không tìm thấy",
     *     )
     * )
     */
    public function findById(string $id)
    {
        // Tìm bài viết theo ID
        $post = Post::find($id);
        if (!$post) {
            return ApiResponse::error('Bài viết không tìm thấy', 404);
        }

        return ApiResponse::success($post, 'Bài viết tìm thấy');
    }
}
