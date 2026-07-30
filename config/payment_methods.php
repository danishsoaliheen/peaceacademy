<?php
// config/payment_methods.php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Methods
    |--------------------------------------------------------------------------
    | This is the master list. Runtime overrides (enable/disable/reorder)
    | are stored in storage/app/payment_methods.json
    */
    'methods' => [
        ['key' => 'cash',            'label' => 'Cash',                  'icon' => 'fa-money-bill-wave',  'enabled' => true],
        ['key' => 'easypaisa',       'label' => 'EasyPaisa',             'icon' => 'fa-mobile-alt',       'enabled' => true],
        ['key' => 'jazzcash',        'label' => 'JazzCash',              'icon' => 'fa-mobile-alt',       'enabled' => true],
        ['key' => 'bank_transfer',   'label' => 'Bank Transfer',         'icon' => 'fa-university',       'enabled' => true],
        ['key' => 'cheque',          'label' => 'Cheque',                'icon' => 'fa-file-alt',         'enabled' => true],
        ['key' => 'ibft',            'label' => 'Online Transfer / IBFT','icon' => 'fa-exchange-alt',     'enabled' => true],
        ['key' => 'card',            'label' => 'Credit / Debit Card',   'icon' => 'fa-credit-card',      'enabled' => true],
        ['key' => 'funds',           'label' => 'Funds',                 'icon' => 'fa-hand-holding-usd', 'enabled' => true],
    ],
];