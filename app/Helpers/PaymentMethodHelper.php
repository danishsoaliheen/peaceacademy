<?php
// app/Helpers/PaymentMethodHelper.php

namespace App\Helpers;

class PaymentMethodHelper
{
    protected static string $overridePath = 'payment_methods.json';

    /**
     * Get all methods, merging config defaults with any saved overrides.
     */
    public static function all(): array
    {
        $defaults = collect(config('payment_methods.methods'))
            ->keyBy('key')
            ->toArray();

        $overridePath = storage_path('app/' . static::$overridePath);

        if (file_exists($overridePath)) {
            $saved = json_decode(file_get_contents($overridePath), true) ?? [];
            // Merge saved order/enabled state into defaults
            $merged = [];
            foreach ($saved as $item) {
                $key = $item['key'];
                if (isset($defaults[$key])) {
                    $merged[] = array_merge($defaults[$key], [
                        'enabled' => $item['enabled'],
                        'label'   => $item['label'] ?? $defaults[$key]['label'],
                    ]);
                    unset($defaults[$key]);
                }
            }
            // Append any new methods from config not yet in saved list
            foreach ($defaults as $method) {
                $merged[] = $method;
            }
            return $merged;
        }

        return array_values($defaults);
    }

    /**
     * Get only enabled methods (for dropdowns).
     */
    public static function enabled(): array
    {
        return array_values(array_filter(static::all(), fn($m) => $m['enabled']));
    }

    /**
     * Save the full methods array to the override file.
     */
    public static function save(array $methods): bool
    {
        $path = storage_path('app/' . static::$overridePath);
        return file_put_contents($path, json_encode($methods, JSON_PRETTY_PRINT)) !== false;
    }
}