<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Store a newly created order.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_email' => 'required|email',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|min:1',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            // Calculate total amount by fetching product prices from catalog service
            $totalAmount = 0;
            $orderItems = [];

            foreach ($validated['items'] as $item) {
                // Fetch product details from catalog service
                $catalogServiceUrl = env('CATALOG_SERVICE_URL', 'http://127.0.0.1:8001');
                // Normalize localhost to 127.0.0.1 for better reliability on Windows
                $catalogServiceUrl = str_replace('localhost', '127.0.0.1', $catalogServiceUrl);
                $url = "{$catalogServiceUrl}/api/products/{$item['product_id']}";
                
                $product = null;
                
                // Try HTTP client first, fallback to file_get_contents
                try {
                    $productResponse = Http::timeout(10)
                        ->retry(2, 100)
                        ->get($url);
                    
                    if ($productResponse->successful()) {
                        $product = $productResponse->json();
                    } else {
                        throw new \Exception('HTTP request not successful: ' . $productResponse->status());
                    }
                } catch (\Exception $httpException) {
                    // Fallback to file_get_contents if HTTP client fails
                    Log::warning('HTTP client failed, trying file_get_contents: ' . $httpException->getMessage());
                    $json = @file_get_contents($url);
                    
                    if ($json === false) {
                        DB::rollBack();
                        Log::error('Failed to fetch product from catalog service using both methods');
                        Log::error('URL: ' . $url);
                        return response()->json([
                            'error' => 'Catalog service unavailable. Please ensure the catalog service is running on port 8001.',
                        ], 503);
                    }
                    
                    $product = json_decode($json, true);
                    if (!$product || !isset($product['price'])) {
                        DB::rollBack();
                        Log::error('Invalid product data from catalog service: ' . $json);
                        return response()->json(['error' => 'Invalid product data'], 500);
                    }
                }
                
                if (!isset($product['price'])) {
                    DB::rollBack();
                    Log::error('Product missing price: ' . json_encode($product));
                    return response()->json(['error' => 'Invalid product data'], 500);
                }
                
                $itemTotal = (float)$product['price'] * $item['quantity'];
                $totalAmount += $itemTotal;

                $orderItems[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product['price'],
                ];
            }

            // Create order
            $order = Order::create([
                'user_email' => $validated['user_email'],
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            // Create order items
            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            DB::commit();

            // Call Email Service asynchronously (fire and forget)
            $this->sendOrderEmail($order, $orderItems);

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order->load('items'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Failed to create order',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send order email via Email Service.
     */
    private function sendOrderEmail(Order $order, array $orderItems): void
    {
        try {
            $emailServiceUrl = env('EMAIL_SERVICE_URL', 'http://localhost:8003');
            
            Http::timeout(5)->post("{$emailServiceUrl}/api/send-order-email", [
                'user_email' => $order->user_email,
                'order' => [
                    'id' => $order->id,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                ],
                'items' => $orderItems,
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the order creation
            Log::error('Failed to send order email: ' . $e->getMessage());
        }
    }
}
