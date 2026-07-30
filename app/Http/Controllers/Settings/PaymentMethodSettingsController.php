<?php
// app/Http/Controllers/Settings/PaymentMethodSettingsController.php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Helpers\PaymentMethodHelper;
use Illuminate\Http\Request;

class PaymentMethodSettingsController extends Controller
{
    public function index()
    {
        $methods = PaymentMethodHelper::all();
        return view('settings.payment-methods', compact('methods'));
    }

    public function update(Request $request)
    {
        $incoming = $request->input('methods', []);

        // Build ordered, sanitized array
        $methods = [];
        foreach ($incoming as $item) {
            $methods[] = [
                'key'     => $item['key'],
                'label'   => trim($item['label']),
                'icon'    => $item['icon'],
                'enabled' => isset($item['enabled']) && $item['enabled'] == '1',
            ];
        }

        PaymentMethodHelper::save($methods);

        return redirect()->route('settings.payment-methods.index')
            ->with('success', 'Payment methods updated successfully.');
    }
}