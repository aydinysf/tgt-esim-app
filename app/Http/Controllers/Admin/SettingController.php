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

        return view('admin.settings.index', compact('environment', 'baseUrl', 'accountId', 'secret', 'accountBalance'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'tgt_environment' => 'required|in:sandbox,production',
            'tgt_base_url' => 'required|url',
            'tgt_account_id' => 'required|string',
            'tgt_secret' => 'required|string',
        ]);

        Setting::set('tgt_environment', $validated['tgt_environment']);
        Setting::set('tgt_base_url', $validated['tgt_base_url']);
        Setting::set('tgt_account_id', $validated['tgt_account_id']);
        Setting::set('tgt_secret', $validated['tgt_secret']);

        // Clear cached token when credentials change
        \Illuminate\Support\Facades\Cache::forget('tgt_access_token_' . md5($validated['tgt_account_id']));

        return redirect()->route('admin.settings.index')
            ->with('success', 'TGT API ayarları başarıyla güncellendi.');
    }
}
