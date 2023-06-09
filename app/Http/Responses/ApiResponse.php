<?php

namespace App\Http\Responses;


class ApiResponse
{
    public static function success($message , $data , $status )
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status );
    }

    public static function error($message , $errors , $status )
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

}
