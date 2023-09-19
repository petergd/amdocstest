<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{
    /**
     * Get all tags
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
		$response = DB::select("CALL getTags()")[0];
		if(empty($response) or !is_object($response)) {
			return response()->json(['code' => 500, 'message' => 'Internal Server Error. Server no data were returned!']);
		}
		$tags = json_decode($response->tags);
		return response()->json($tags);
    }
}
