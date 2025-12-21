<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    // List all products
    public function index()
    {
        try {
            $products = $this->productService->all(); // get all products

            return response()->json([
                'success' => true,
                'data' => $products
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Failed to fetch products', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products.'
            ], 500);
        }
    }

    // Get specific product by ID
    public function show($id)
    {
        try {
            $product = $this->productService->find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $product
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Failed to fetch product', [
                'product_id' => $id,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch product.'
            ], 500);
        }
    }
}
