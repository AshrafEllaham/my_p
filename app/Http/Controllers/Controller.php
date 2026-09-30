<?php

namespace App\Http\Controllers;

use Illuminate\Http\Exceptions\HttpResponseException;

abstract class Controller
{
    protected function dashBoardJson(
        int $status = 200,
        ?string $message = null,
        mixed $data = null,
    ): never {
        throw new HttpResponseException(response()->json([
            'status' => $status >= 200 && $status < 300,
            'message' => $message,
            'data' => $data,
        ], $status));
    }
}
