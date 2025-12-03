<?php

namespace App\Http\Controllers;

use App\Mail\OrderSummaryMail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailController extends Controller
{
    /**
     * Send order summary email.
     */
    public function sendOrderEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_email' => 'required|email',
            'order' => 'required|array',
            'order.id' => 'required|integer',
            'order.total_amount' => 'required|numeric',
            'items' => 'required|array',
        ]);

        try {
            Mail::to($validated['user_email'])->send(
                new OrderSummaryMail($validated['order'], $validated['items'])
            );

            return response()->json([
                'message' => 'Order summary email sent successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to send email: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to send email',
            ], 500);
        }
    }
}
