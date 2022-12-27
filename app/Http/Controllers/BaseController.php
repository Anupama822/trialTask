<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function successResponse(array $data)
    {
        return response()->json([
            'status'   => true,
            'user'     => $data['user'] ?? null,
            'token'    => $data['token'] ?? null,
            'message'  => $data['message'] ?? null,
        ]);
    }
    public function errorResponse(string $message)
    {
        $response['message'] = $message;
        $response['status'] = false;
        return $response;
    }
}
