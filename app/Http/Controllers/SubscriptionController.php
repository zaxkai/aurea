<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Snap;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    // Tampilkan halaman upgrade premium
    public function index()
    {
        $user = auth()->user();
        $activeSubscription = $user->isPremium()
            ? $user->subscriptions()->where('status', 'active')->where('expires_at', '>', now())->first()
            : null;

        return view('subscription.index', compact('activeSubscription'));
    }

    // User klik "Upgrade" -> generate order & redirect ke pembayaran
    public function checkout(Request $request)
    {
        $user = auth()->user();
        $orderId = 'AUREA-'.$user->id.'-'.time();
        $price = 29000; // harga langganan bulanan, sesuaikan

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'midtrans_order_id' => $orderId,
            'status' => 'pending',
        ]);

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $price,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        return view('subscription.checkout', compact('snapToken', 'subscription'));
    }

    // Endpoint webhook, dipanggil otomatis oleh Midtrans setelah pembayaran
    public function webhook(Request $request)
    {
        $notif = new Notification;

        $orderId = $notif->order_id;
        $status = $notif->transaction_status;
        $fraud = $notif->fraud_status;

        $subscription = Subscription::where('midtrans_order_id', $orderId)->first();

        if (! $subscription) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($status === 'capture' || $status === 'settlement') {
            if ($fraud === 'accept' || is_null($fraud)) {
                $subscription->update([
                    'status' => 'active',
                    'started_at' => now(),
                    'expires_at' => now()->addDays(30),
                ]);
            }
        } elseif ($status === 'deny' || $status === 'cancel' || $status === 'expire') {
            $subscription->update(['status' => 'failed']);
        }

        return response()->json(['message' => 'OK']);
    }
}
