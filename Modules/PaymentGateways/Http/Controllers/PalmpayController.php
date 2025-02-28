<?php

namespace Modules\PaymentGateways\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;

class PalmpayController extends Controller
{
    private $baseUrl;
    private $clientId;
    private $clientSecret;
    private $merchantId;
    private $notifyUrl;
    private $returnUrl;

    public function __construct()
    {
        $this->baseUrl = env('PALMPAY_BASE_URL');
        $this->clientId = env('PALMPAY_CLIENT_ID');
        $this->clientSecret = env('PALMPAY_CLIENT_SECRET');
        $this->merchantId = env('PALMPAY_MERCHANT_ID');
        $this->notifyUrl = env('PALMPAY_NOTIFY_URL');
        $this->returnUrl = env('PALMPAY_RETURN_URL');
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('paymentgateways::index');
    }


    /**
     * Create an order for PalmPay Checkout
     */
    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'currency' => 'required|string|in:NGN',
            'reference' => 'required|string|unique:orders,reference',
        ]);

        $orderData = [
            'merchantId' => $this->merchantId,
            'orderAmount' => $validated['amount'],
            'orderCurrency' => $validated['currency'],
            'orderId' => $validated['reference'],
            'notifyUrl' => $this->notifyUrl,
            'returnUrl' => $this->returnUrl,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
            ])->post("{$this->baseUrl}/palmpay-order/create", $orderData);

            $responseBody = $response->json();

            if ($response->successful() && $responseBody['status'] === 'SUCCESS') {
                return redirect()->away($responseBody['data']['checkoutUrl']);
            }

            return back()->withErrors(['error' => $responseBody['message'] ?? 'Failed to create order.']);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle PalmPay Notification Callback
     */
    public function handleNotification(Request $request)
    {
        $data = $request->all();

        // Verify the notification (optional: implement signature verification)
        if ($data['status'] === 'SUCCESS') {
            // Update order status in your database
            // Example: Order::where('reference', $data['orderId'])->update(['status' => 'paid']);
            return response()->json(['message' => 'Notification processed successfully.']);
        }

        return response()->json(['message' => 'Failed to process notification.'], 400);
    }

    /**
     * Handle Return URL (optional)
     */
    public function handleReturn(Request $request)
    {
        $data = $request->all();

        if ($data['status'] === 'SUCCESS') {
            return view('payment.success', ['data' => $data]);
        }

        return view('payment.failed', ['data' => $data]);
    }

    /**
     * Query the status of an order
     */
    public function queryOrder(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string|exists:orders,reference', // Validate against your database records
        ]);

        $orderReference = $validated['reference'];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
            ])->get("{$this->baseUrl}/palmpay-order/query", [
                'orderId' => $orderReference,
            ]);

            $responseBody = $response->json();

            if ($response->successful() && $responseBody['status'] === 'SUCCESS') {
                return response()->json([
                    'message' => 'Order status retrieved successfully.',
                    'data' => $responseBody['data'],
                ]);
            }

            return response()->json([
                'message' => $responseBody['message'] ?? 'Failed to query order status.',
                'data' => $responseBody,
            ], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

     /**
     * Create an order for PalmPay Wallet Payment
     */
    public function createWalletOrder(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string|in:NGN',
            'reference' => 'required|string|unique:orders,reference',
            'phoneNumber' => 'required|string|regex:/^[0-9]{10,14}$/',
        ]);

        $orderData = [
            'merchantId' => $this->merchantId,
            'orderAmount' => $validated['amount'],
            'orderCurrency' => $validated['currency'],
            'orderId' => $validated['reference'],
            'notifyUrl' => $this->notifyUrl,
            'phoneNumber' => $validated['phoneNumber'],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
            ])->post("{$this->baseUrl}/wallet-order/create", $orderData);

            $responseBody = $response->json();

            if ($response->successful() && $responseBody['status'] === 'SUCCESS') {
                return response()->json([
                    'message' => 'Wallet order created successfully.',
                    'data' => $responseBody['data'],
                ]);
            }

            return response()->json([
                'message' => $responseBody['message'] ?? 'Failed to create wallet order.',
                'data' => $responseBody,
            ], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Query the status of a PalmPay wallet order
     */
    public function queryWalletOrder(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string|exists:orders,reference', // Validate against your database records
        ]);

        $orderReference = $validated['reference'];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
            ])->get("{$this->baseUrl}/wallet-order/query", [
                'orderId' => $orderReference,
            ]);

            $responseBody = $response->json();

            if ($response->successful() && $responseBody['status'] === 'SUCCESS') {
                return response()->json([
                    'message' => 'Order status retrieved successfully.',
                    'data' => $responseBody['data'],
                ]);
            }

            return response()->json([
                'message' => $responseBody['message'] ?? 'Failed to query order status.',
                'data' => $responseBody,
            ], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

     /**
     * Create an order for PalmPay Bank Transfer Payment
     */
    public function createBankTransferOrder(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string|in:NGN',
            'reference' => 'required|string|unique:orders,reference',
        ]);

        $orderData = [
            'merchantId' => $this->merchantId,
            'orderAmount' => $validated['amount'],
            'orderCurrency' => $validated['currency'],
            'orderId' => $validated['reference'],
            'notifyUrl' => $this->notifyUrl,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
            ])->post("{$this->baseUrl}/bank-transfer/create-order", $orderData);

            $responseBody = $response->json();

            if ($response->successful() && $responseBody['status'] === 'SUCCESS') {
                return response()->json([
                    'message' => 'Bank transfer order created successfully.',
                    'data' => $responseBody['data'],
                ]);
            }

            return response()->json([
                'message' => $responseBody['message'] ?? 'Failed to create bank transfer order.',
                'data' => $responseBody,
            ], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

     /**
     * Query the status of a PalmPay Bank Transfer Order
     */
    public function queryBankTransferOrder(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string|exists:orders,reference', // Validate against your database records
        ]);

        $orderReference = $validated['reference'];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
            ])->get("{$this->baseUrl}/bank-transfer/query-order", [
                'orderId' => $orderReference,
            ]);

            $responseBody = $response->json();

            if ($response->successful() && $responseBody['status'] === 'SUCCESS') {
                return response()->json([
                    'message' => 'Order status retrieved successfully.',
                    'data' => $responseBody['data'],
                ]);
            }

            return response()->json([
                'message' => $responseBody['message'] ?? 'Failed to query order status.',
                'data' => $responseBody,
            ], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

