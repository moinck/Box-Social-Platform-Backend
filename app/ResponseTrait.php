<?php

namespace App;

trait ResponseTrait
{
    /**
     * Function for success response
     * @param mixed $data
     * @param mixed $message
     * @param mixed $code
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function success($data = [], $message = 'Success', $code = 200)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $code);
    }

    /**
     * Function for error response
     * @param mixed $message
     * @param mixed $code
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function error($message = 'Error', $code = 500)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }

    /**
     * Function for validation error response
     * @param mixed $message
     * @param mixed $errors
     * @param mixed $code
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function validationError($message = 'Validation errors', $errors = [], $code = 422)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }
}
