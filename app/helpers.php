<?php 
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Response;


/**
 * @param string $message
 * @param array $data
 * @param int $statusCode
 * @return \Illuminate\Http\JsonResponse
 */
function successResponse($message = 'Success', $data = [], int $statusCode = Response::HTTP_OK): \Illuminate\Http\JsonResponse
{
    return ApiResponse::success($message, $data, $statusCode);
}


/**
 * @param string $message
 * @param array $errors
 * @param int $statusCode
 * @return \Illuminate\Http\JsonResponse
 */
function errorResponse($message = 'Error', $errors = [], int $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY): \Illuminate\Http\JsonResponse
{
    return ApiResponse::error($message, $errors, $statusCode);
}