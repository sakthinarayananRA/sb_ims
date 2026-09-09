<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Low Stock Threshold
    |--------------------------------------------------------------------------
    |
    | When a product does not have an explicit threshold or when querying
    | global low-stock items without parameters, this threshold is used.
    |
    */
    'default_low_stock_threshold' => env('INVENTORY_LOW_STOCK_THRESHOLD', 10),

    /*
    |--------------------------------------------------------------------------
    | Order Number Prefix
    |--------------------------------------------------------------------------
    |
    | The prefix prepended to system generated order numbers.
    | Example: ORD-20260909-0001
    |
    */
    'order_prefix' => env('INVENTORY_ORDER_PREFIX', 'ORD'),

    /*
    |--------------------------------------------------------------------------
    | Currency Configuration
    |--------------------------------------------------------------------------
    */
    'currency' => [
        'code' => 'INR',
        'symbol' => '₹',
    ],
];

