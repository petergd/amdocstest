<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Get all products
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
		$response = DB::select('call manageProducts(?,?,?,?,?,?,?,?)',[null,'','','',null,null,'','get'])[0];
		if(empty($response) or !is_object($response)) {
			return response()->json(['code' => 500, 'message' => 'Internal Server Error. Server no data were returned!']);
		}
		$products = $response->products;
		return response()->json($products);
    }
	
	/**
     * Get products by category
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function get(Request $request, $category): JsonResponse
    {
        $response = DB::select('call manageProducts(?,?,?,?,?,?,?,?)',[null,'','',$category,null,null,'','get'])[0];
		if(empty($response) or !is_object($response)) {
			return response()->json(['code' => 500, 'message' => 'Internal Server Error. Server no data were returned!']);
		}
		$products = $response->products;
		return response()->json($products);
    }
	
	
	/**
     * Add product
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function add(Request $request)
    {
        $validator = Validator::make($request->post(), [
            'name' => 'min:3|max:255',
            'code' => 'min:3|max:255',
            'category' => 'min:3|max:120',
			'price' => 'min:4|max:20',
            'release_date' => 'min:3|max:120',
            'tags' => 'min:3|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
		$data = $request->json()->all();
        try {
            $response = DB::select('call manageProducts(?,?,?,?,?,?,?,?)',[null,$data['name'],$data['code'],$data['category'],$data['price'],$data['release_date'],$data['tags'],'create'])[0];
			if(empty($response) or !is_object($response)) {
				return response()->json(['code' => 500, 'message' => 'Internal Server Error. Server no data were returned!']);
			}
			$products = $response->products;
			return response()->json($products);

        } catch (Exception $e) {
            Log::debug('error creating product');
        }

        return response()->json('error_creating', 400);
    }

    /**
     * Update product
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->post(), [
			'pid' => 'min:1|max:30',
            'name' => 'min:0|max:255',
            'code' => 'min:0|max:255',
            'category' => 'min:0|max:120',
			'price' => 'min:0|max:20',
            'release_date' => 'min:0|max:120',
            'tags' => 'min:0|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
		$data = $request->json()->all();
        try {
            $response = DB::select('call manageProducts(?,?,?,?,?,?,?,?)',[$data['pid'],$data['name'],$data['code'],$data['category'],$data['price'],$data['release_date'],$data['tags'],'update'])[0];
			if(empty($response) or !is_object($response)) {
				return response()->json(['code' => 500, 'message' => 'Internal Server Error. Server no data were returned!']);
			}
			$products = $response->products;
			return response()->json($products);

        } catch (Exception $e) {
            Log::debug('error updating product');
        }

        return response()->json('error_updating', 400);
    }
}
