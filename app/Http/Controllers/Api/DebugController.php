<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DebugController extends Controller
{
    public function testJson(Request $request): JsonResponse
    {
        $raw = $request->getContent();
        $phpInput = @file_get_contents('php://input');
        $jsonFromContent = $raw ? json_decode($raw, true) : null;
        $jsonFromPhpInput = $phpInput ? json_decode($phpInput, true) : null;

        return response()->json([
            'getContent_length' => strlen($raw ?? ''),
            'getContent_raw' => substr($raw ?? '', 0, 500),
            'getContent_json' => $jsonFromContent,
            'phpInput_length' => strlen($phpInput ?? ''),
            'phpInput_raw' => substr($phpInput ?? '', 0, 500),
            'phpInput_json' => $jsonFromPhpInput,
            'request_all' => $request->all(),
            'request_input' => $request->input(),
            'is_json' => $request->isJson(),
            'content_type' => $request->header('Content-Type'),
            'method' => $request->method(),
        ]);
    }
}
