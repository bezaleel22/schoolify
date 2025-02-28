<?php

namespace Modules\PaymentGateways\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MonnifyController extends Controller
{
    // Replace these with your Monnify credentials
    private $baseUrl = 'https://sandbox.monnify.com/api/v1'; // Use production URL for live mode
    private $apiKey = 'YOUR_API_KEY';
    private $secretKey = 'YOUR_SECRET_KEY';
    private $contractCode = 'YOUR_CONTRACT_CODE';

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('paymentgateways::index');
    }

    /**
     * Generate a payment request
     */
    public function initializePayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'customerName' => 'required|string',
            'customerEmail' => 'required|email',
        ]);

        $data = [
            'amount' => $request->amount,
            'customerName' => $request->customerName,
            'customerEmail' => $request->customerEmail,
            'paymentReference' => uniqid('PAY_'), // Unique reference
            'paymentDescription' => 'Payment for services',
            'currencyCode' => 'NGN',
            'contractCode' => $this->contractCode,
            'redirectUrl' => route('monnify.callback'), // Callback URL
        ];

        $response = Http::withBasicAuth($this->apiKey, $this->secretKey)
            ->post("{$this->baseUrl}/merchant/transactions/init-transaction", $data);

        if ($response->successful() && $response['requestSuccessful']) {
            return redirect()->away($response['responseBody']['checkoutUrl']);
        }

        return back()->with('error', 'Failed to initialize payment. Please try again.');
    }

    /**
     * Handle callback from Monnify
     */
    public function handleCallback(Request $request)
    {
        $transactionReference = $request->query('transactionReference');

        // Verify transaction
        $response = Http::withBasicAuth($this->apiKey, $this->secretKey)
            ->get("{$this->baseUrl}/merchant/transactions/query?transactionReference={$transactionReference}");

        if ($response->successful() && $response['requestSuccessful']) {
            $transactionStatus = $response['responseBody']['paymentStatus'];
            if ($transactionStatus === 'PAID') {
                // Update database or take appropriate action
                return redirect()->route('payment.success');
            }
        }

        return redirect()->route('payment.failed');
    }

    /**
     * Display success page
     */
    public function paymentSuccess()
    {
        return view('payments.success');
    }

    /**
     * Display failure page
     */
    public function paymentFailed()
    {
        return view('payments.failed');
    }


}
