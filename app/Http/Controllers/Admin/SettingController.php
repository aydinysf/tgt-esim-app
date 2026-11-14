<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\TgtEsimService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(TgtEsimService $tgtService)
    {
        $environment = Setting::get('tgt_environment', 'sandbox');
        $baseUrl = Setting::get('tgt_base_url', 'https://enterpriseapisandbox.tugegroup.com:8070/openapi');
        $accountId = Setting::get('tgt_account_id', 'TGT_Channel_Demo');
        $secret = Setting::get('tgt_secret', 'jzXUuQVIlFwf3peM');

        $accountBalance = $tgtService->getAccountBalance();

        // IWC Settings
        $iwcBaseUrl = Setting::get('iwc_base_url', config('services.iwc.base_url'));
        $iwcProfileId = Setting::get('iwc_profile_id', config('services.iwc.profile_id'));
        $iwcAccessKey = Setting::get('iwc_access_key', config('services.iwc.access_key'));

        // Stripe Settings
        $stripeKey = Setting::get('stripe_key', config('services.stripe.key'));
        $stripeSecret = Setting::get('stripe_secret', config('services.stripe.secret'));

        return view('admin.settings.index', compact(
            'environment', 'baseUrl', 'accountId', 'secret', 'accountBalance',
            'iwcBaseUrl', 'iwcProfileId', 'iwcAccessKey',
            'stripeKey', 'stripeSecret'
        ));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'tgt_environment' => 'nullable|in:sandbox,production',
            'tgt_base_url' => 'nullable|url',
            'tgt_account_id' => 'nullable|string',
            'tgt_secret' => 'nullable|string',
            'iwc_base_url' => 'nullable|url',
            'iwc_profile_id' => 'nullable|string',
            'iwc_access_key' => 'nullable|string',
            'stripe_key' => 'nullable|string',
            'stripe_secret' => 'nullable|string',
        ]);

        if (isset($validated['tgt_environment'])) Setting::set('tgt_environment', $validated['tgt_environment']);
        if (isset($validated['tgt_base_url'])) Setting::set('tgt_base_url', $validated['tgt_base_url']);
        if (isset($validated['tgt_account_id'])) Setting::set('tgt_account_id', $validated['tgt_account_id']);
        if (isset($validated['tgt_secret'])) Setting::set('tgt_secret', $validated['tgt_secret']);

        if (isset($validated['iwc_base_url'])) Setting::set('iwc_base_url', $validated['iwc_base_url']);
        if (isset($validated['iwc_profile_id'])) Setting::set('iwc_profile_id', $validated['iwc_profile_id']);
        if (isset($validated['iwc_access_key'])) Setting::set('iwc_access_key', $validated['iwc_access_key']);

        if (isset($validated['stripe_key'])) Setting::set('stripe_key', $validated['stripe_key']);
        if (isset($validated['stripe_secret'])) Setting::set('stripe_secret', $validated['stripe_secret']);

        // Clear cached token when credentials change
        if (!empty($validated['tgt_account_id'])) {
            \Illuminate\Support\Facades\Cache::forget('tgt_access_token_' . md5($validated['tgt_account_id']));
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'API ayarları başarıyla güncellendi.');
    }
}
