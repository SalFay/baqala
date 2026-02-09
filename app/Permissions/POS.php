<?php

return [
    'title' => 'Point of Sale',
    'description' => 'Manage POS Terminal Permissions',
    'permissions' => [
        'access pos' => [
            'title' => 'Access POS Terminal',
            'description' => 'Access the point of sale terminal',
        ],
        'apply discount' => [
            'title' => 'Apply Discount',
            'description' => 'Apply discounts to cart items or orders',
        ],
        'hold order' => [
            'title' => 'Hold Order',
            'description' => 'Put orders on hold and restore them',
        ],
        'void item' => [
            'title' => 'Void Item',
            'description' => 'Remove items from cart',
        ],
        'open cash drawer' => [
            'title' => 'Open Cash Drawer',
            'description' => 'Open the cash drawer manually',
        ],
        'process refund at pos' => [
            'title' => 'Process Refund at POS',
            'description' => 'Process quick refunds at the POS',
        ],
        'view held orders' => [
            'title' => 'View Held Orders',
            'description' => 'View and restore held orders',
        ],
        'print receipt' => [
            'title' => 'Print Receipt',
            'description' => 'Print sales receipts',
        ],
    ],
];
