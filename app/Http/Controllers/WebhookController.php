<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\TgtEsimService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request, TgtEsimService $tgtService)
    {
        $payload = $request->all();
        Log::info('TGT Webhook Received: ', $payload);

        // Optional signature verification if sign present
        if (isset($payload['sign'])) {
            $isValid = $tgtService->verifyWebhookSignature($payload);
            if (!$isValid) {
                Log::warning('TGT Webhook signature verification failed.');
            }
        }

        // Extract Order details from Webhook callback (Section 5.1 of PDF)
        $data = $payload['data'] ?? [];
        $orderInfo = $data['orderInfo'] ?? [];

        $channelOrderNo = $orderInfo['channelOrderNo'] ?? ($data['channelOrderNo'] ?? null);
        $orderNo = $orderInfo['orderNo'] ?? ($data['orderNo'] ?? null);

        if ($channelOrderNo || $orderNo) {
            $order = Order::where('channel_order_no', $channelOrderNo)
                ->orWhere('order_no', $orderNo)
                ->first();

            if ($order) {
                if (!empty($orderInfo['iccid'])) {
                    $order->iccid = $orderInfo['iccid'];
                }
                if (!empty($orderInfo['qrCode'])) {
                    $order->qr_code = $orderInfo['qrCode'];
                }
                if (!empty($orderInfo['orderStatus'])) {
                    $order->order_status = $orderInfo['orderStatus'];
                }
                if (!empty($orderInfo['profileStatus'])) {
                    $order->profile_status = $orderInfo['profileStatus'];
                }
                $order->save();
            }
        }

        // TGT platform MANDATORILY expects {"code":"0000","msg":"success"}
        return response()->json([
            'code' => '0000',
            'msg' => 'success',
        ]);
    }
}
