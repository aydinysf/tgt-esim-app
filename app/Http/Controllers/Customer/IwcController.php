<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\IwcOrder;
use App\Models\IwcProduct;
use App\Services\IwcService;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IwcController extends Controller
{
    /**
     * List all available IWC Experience & Tour products
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $effectiveCustomer = $user->getEffectiveCustomer();

        $search = trim((string) $request->query('search', ''));
        $sort = $request->query('sort', 'default');

        $query = IwcProduct::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        } else {
            $query->orderBy('id', 'desc');
        }

        $products = $query->paginate(12)->withQueryString();

        // Count of customer's tour orders
        $tourOrdersCount = IwcOrder::where('user_id', $effectiveCustomer->id)->count();

        return view('customer.iwc.index', compact('products', 'effectiveCustomer', 'tourOrdersCount', 'search', 'sort'));
    }

    /**
     * Show single product details & booking form
     */
    public function show($id, Request $request, IwcService $iwcService)
    {
        $user = Auth::user();
        $effectiveCustomer = $user->getEffectiveCustomer();

        $product = IwcProduct::findOrFail($id);

        // Fetch live availability from IWC if configured
        $liveData = null;
        if ($iwcService->isConfigured()) {
            $startDate = now()->format('Y-m-d');
            $endDate = now()->addDays(30)->format('Y-m-d');
            $apiResp = $iwcService->getProduct($id, $startDate, $endDate);
            if (!empty($apiResp['status']) && !empty($apiResp['data'])) {
                $liveData = $apiResp['data'];
            }
        }

        $variants = $product->variants_data ?? [];
        if (empty($variants) && !empty($liveData['variants'])) {
            $variants = $liveData['variants'];
        }

        return view('customer.iwc.show', compact('product', 'variants', 'effectiveCustomer', 'liveData'));
    }

    /**
     * AJAX endpoint to query real-time time slots for a specific date
     */
    public function getAvailability(Request $request, $id, IwcService $iwcService)
    {
        $date = $request->query('date');
        if (!$date) {
            return response()->json(['status' => false, 'message' => 'Tarih belirtilmedi.'], 400);
        }

        $slots = [];

        if ($iwcService->isConfigured()) {
            $apiResp = $iwcService->getProduct($id, $date, $date);
            if (!empty($apiResp['status']) && !empty($apiResp['data']['availabilities'][$date])) {
                $slots = $apiResp['data']['availabilities'][$date];
            }
        }

        // If no slots returned from live API, check product raw_data fallback or generate available slots
        if (empty($slots)) {
            $product = IwcProduct::find($id);
            if ($product && !empty($product->raw_data['availabilities'][$date])) {
                $slots = $product->raw_data['availabilities'][$date];
            } else {
                // Default daylight/evening sample slots for demonstration
                $slots = [
                    ['start_time' => '10:00', 'end_time' => '10:00', 'available' => 10, 'status' => 'available'],
                    ['start_time' => '14:00', 'end_time' => '14:00', 'available' => 8, 'status' => 'available'],
                    ['start_time' => '19:00', 'end_time' => '19:00', 'available' => 12, 'status' => 'available'],
                ];
            }
        }

        return response()->json([
            'status' => true,
            'date' => $date,
            'slots' => $slots
        ]);
    }

    /**
     * Initiate Stripe Checkout for tour booking
     */
    public function checkout(Request $request, StripeService $stripeService)
    {
        $user = Auth::user();
        $effectiveCustomer = $user->getEffectiveCustomer();

        $validated = $request->validate([
            'product_id' => 'required|exists:iwc_products,id',
            'product_variant_id' => 'required',
            'service_date' => 'required|date',
            'service_time' => 'required|string',
            'quantity' => 'required|integer|min:1|max:50',
            'customer_first_name' => 'required|string|max:100',
            'customer_last_name' => 'required|string|max:100',
            'customer_email' => 'required|email|max:150',
            'customer_phone' => 'required|string|max:30',
            'hotel_name' => 'nullable|string|max:200',
        ]);

        $product = IwcProduct::findOrFail($validated['product_id']);

        // Find selected variant price & label
        $variantLabel = 'Standart Katılım';
        $unitPrice = (float) $product->price;

        if (!empty($product->variants_data)) {
            foreach ($product->variants_data as $v) {
                if ($v['id'] == $validated['product_variant_id']) {
                    $variantLabel = is_array($v['label']) ? ($v['label']['tr'] ?? $v['label']['en'] ?? reset($v['label'])) : ($v['label'] ?? 'Varyant');
                    $unitPrice = (float) ($v['price']['amount'] ?? $product->price);
                    break;
                }
            }
        }

        $totalAmount = $unitPrice * $validated['quantity'];
        $orderNo = 'IWC-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        $fullServiceDate = $validated['service_date'] . ' ' . $validated['service_time'];

        $requiredData = [];
        if (!empty($validated['hotel_name'])) {
            $requiredData[] = [
                'key' => 'hotel_name',
                'value' => $validated['hotel_name']
            ];
        }

        $branch = null;
        if ($user->isBranchUser() && $user->branch) {
            $branch = $user->branch;
        }

        // Create pending IwcOrder record
        $order = IwcOrder::create([
            'order_no' => $orderNo,
            'user_id' => $effectiveCustomer->id,
            'branch_id' => $branch?->id,
            'iwc_product_id' => $product->id,
            'product_name' => $product->name,
            'product_variant_id' => $validated['product_variant_id'],
            'variant_label' => $variantLabel,
            'quantity' => $validated['quantity'],
            'service_date' => $fullServiceDate,
            'customer_first_name' => $validated['customer_first_name'],
            'customer_last_name' => $validated['customer_last_name'],
            'customer_email' => $validated['customer_email'],
            'customer_phone' => $validated['customer_phone'],
            'hotel_name' => $validated['hotel_name'] ?? null,
            'required_data' => $requiredData,
            'total_amount' => $totalAmount,
            'currency' => $product->currency ?: 'EUR',
            'payment_method' => 'stripe',
            'payment_status' => 'pending',
        ]);

        $successUrl = route('customer.iwc.stripe.success', ['orderNo' => $order->order_no]);
        $cancelUrl = route('customer.iwc.stripe.cancel', ['orderNo' => $order->order_no]);

        // If Stripe is configured, create live Stripe Checkout Session
        if ($stripeService->isConfigured()) {
            $sessionResult = $stripeService->createCheckoutSession($order, $successUrl, $cancelUrl);

            if (!$sessionResult['success']) {
                return back()->with('error', $sessionResult['message'])->withInput();
            }

            $order->update(['stripe_session_id' => $sessionResult['session_id']]);
            return redirect()->away($sessionResult['url']);
        }

        // Fallback simulation mode if Stripe credentials not yet set in .env
        $order->update([
            'payment_status' => 'paid',
            'stripe_payment_intent_id' => 'sim_' . Str::random(16),
        ]);

        return redirect()->route('customer.iwc.stripe.success', [
            'orderNo' => $order->order_no,
            'simulated' => 1
        ])->with('info', 'Stripe API anahtarı girilmediği için test modunda ödeme simüle edildi.');
    }

    /**
     * Handle return from Stripe Checkout after successful payment
     */
    public function stripeSuccess(Request $request, $orderNo, IwcService $iwcService, StripeService $stripeService)
    {
        $order = IwcOrder::where('order_no', $orderNo)->firstOrFail();

        // Verify session if live Stripe session was used
        $sessionId = $request->query('session_id');
        if ($sessionId && $stripeService->isConfigured()) {
            $session = $stripeService->retrieveSession($sessionId);
            if ($session && $session->payment_status === 'paid') {
                $order->update([
                    'payment_status' => 'paid',
                    'stripe_payment_intent_id' => is_string($session->payment_intent) ? $session->payment_intent : ($session->payment_intent->id ?? null),
                ]);
            }
        }

        // If payment is confirmed and not yet pushed to IWC, push to IWC API
        if ($order->isPaid() && empty($order->iwc_order_id)) {
            if ($iwcService->isConfigured()) {
                $payload = [
                    'product_id' => (int) $order->iwc_product_id,
                    'first_name' => $order->customer_first_name,
                    'last_name' => $order->customer_last_name,
                    'phone_number' => $order->customer_phone,
                    'email' => $order->customer_email,
                    'service_date' => $order->service_date,
                    'language' => 'en',
                    'external_reference' => $order->order_no,
                    'required_data' => $order->required_data ?? [],
                    'variants' => [
                        [
                            'product_variant_id' => (int) $order->product_variant_id,
                            'quantity' => (int) $order->quantity,
                        ]
                    ],
                    'send_email' => false,
                ];

                $iwcResult = $iwcService->createOrder($payload);

                if (!empty($iwcResult['status']) && !empty($iwcResult['data']['id'])) {
                    $order->update([
                        'iwc_order_id' => $iwcResult['data']['id'],
                        'iwc_order_status' => $iwcResult['data']['status'] ?? 'processing',
                        'iwc_response_data' => $iwcResult['data'],
                    ]);
                } else {
                    $order->update([
                        'iwc_order_status' => 'pending_review',
                        'error_message' => $iwcResult['message'] ?? 'IWC sipariş kaydı beklemede.',
                    ]);
                }
            } else {
                // Mock IWC Order ID for test mode
                $order->update([
                    'iwc_order_id' => rand(150000, 199999),
                    'iwc_order_status' => 'processing',
                    'iwc_response_data' => ['simulated' => true],
                ]);
            }
        }

        return view('customer.iwc.success', compact('order'));
    }

    /**
     * Handle Stripe payment cancel
     */
    public function stripeCancel(Request $request, $orderNo)
    {
        $order = IwcOrder::where('order_no', $orderNo)->first();
        if ($order && $order->payment_status === 'pending') {
            $order->update(['payment_status' => 'failed', 'error_message' => 'Ödeme kullanıcı tarafından iptal edildi.']);
        }

        return redirect()->route('customer.iwc.index')->with('error', 'Kredi kartı ile ödeme işlemi iptal edildi.');
    }

    /**
     * List all purchased IWC tour bookings
     */
    public function orders(Request $request)
    {
        $user = Auth::user();
        $effectiveCustomer = $user->getEffectiveCustomer();

        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');

        $query = IwcOrder::where('user_id', $effectiveCustomer->id)
            ->with(['product', 'branch'])
            ->orderBy('id', 'desc');

        if ($user->isBranchUser() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_no', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%")
                  ->orWhere('customer_first_name', 'like', "%{$search}%")
                  ->orWhere('customer_last_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('payment_status', $status);
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('customer.iwc.orders', compact('orders', 'effectiveCustomer', 'search', 'status'));
    }

    /**
     * Show order voucher details
     */
    public function orderDetail($id)
    {
        $user = Auth::user();
        $effectiveCustomer = $user->getEffectiveCustomer();

        $order = IwcOrder::where('user_id', $effectiveCustomer->id)
            ->with('product')
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'order' => $order
        ]);
    }
}
