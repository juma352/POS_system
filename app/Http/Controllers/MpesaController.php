<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MpesaController extends Controller
{
    public function stkPush(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'phone'  => 'required|string|regex:/^2547\d{8}$/',
        ]);

        $shortcode      = env('MPESA_SHORTCODE');
        $passkey        = env('MPESA_PASSKEY');
        $consumerKey    = env('MPESA_CONSUMER_KEY');
        $consumerSecret = env('MPESA_CONSUMER_SECRET');
        $callbackUrl    = env('MPESA_CALLBACK_URL');

        $timestamp = now()->format('YmdHis');
        $password  = base64_encode($shortcode . $passkey . $timestamp);

        // Step 1: Get access token
        $tokenResponse = Http::withBasicAuth($consumerKey, $consumerSecret)
            ->withOptions(['verify' => false])
            ->get('https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');

        if (!$tokenResponse->successful()) {
            return response()->json([
                'message' => 'Failed to get access token',
                'status'  => $tokenResponse->status(),
                'error'   => $tokenResponse->json(),
            ], 500);
        }

        $accessToken = $tokenResponse->json('access_token');

        if (!$accessToken) {
            return response()->json([
                'message' => 'Access token not found',
                'error'   => $tokenResponse->json(),
            ], 500);
        }

        // Step 2: Send STK Push
        $stkPayload = [
            'BusinessShortCode' => $shortcode,
            'Password'          => $password,
            'Timestamp'         => $timestamp,
            'TransactionType'   => 'CustomerPayBillOnline',
            'Amount'            => $request->amount,
            'PartyA'            => $request->phone,
            'PartyB'            => $shortcode,
            'PhoneNumber'       => $request->phone,
            'CallBackURL'       => $callbackUrl,
            'AccountReference'  => 'POS_' . uniqid(),
            'TransactionDesc'   => 'POS Payment',
        ];

        $stkResponse = Http::withToken($accessToken)
            ->withOptions(['verify' => false])
            ->post('https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest', $stkPayload);

        if ($stkResponse->successful()) {
            return response()->json([
                'message' => 'STK Push initiated',
                'data'    => $stkResponse->json(),
            ]);
        }

        return response()->json([
            'message' => 'STK Push failed',
            'status'  => $stkResponse->status(),
            'error'   => $stkResponse->json(),
        ], 500);
    }

    public function mpesaCallback(Request $request)
    {
        $data = $request->json()->all();

        $stk = $data['Body']['stkCallback'] ?? [];
        $resultCode = $stk['ResultCode'] ?? null;
        $metadata = collect($stk['CallbackMetadata']['Item'] ?? []);

        $receipt = $metadata->firstWhere('Name', 'MpesaReceiptNumber')['Value'] ?? null;
        $amount  = $metadata->firstWhere('Name', 'Amount')['Value'] ?? null;
        $phone   = $metadata->firstWhere('Name', 'PhoneNumber')['Value'] ?? null;

        if ($resultCode === 0) {
            // ✅ Payment success - Save to DB or mark order as paid
            return response()->json([
                'message' => 'Payment successful',
                'receipt' => $receipt,
                'amount'  => $amount,
                'phone'   => $phone,
            ]);
        }

        return response()->json([
            'message'     => 'Payment failed',
            'ResultCode'  => $resultCode,
            'ResultDesc'  => $stk['ResultDesc'] ?? 'Unknown',
        ]);
    }
}
