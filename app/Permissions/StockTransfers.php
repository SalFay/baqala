<?php

return [
    'title' => 'Stock Transfers',
    'description' => 'Manage Stock Transfer Permissions',
    'permissions' => [
        'access stock transfers' => [
            'title' => 'View Stock Transfers',
            'description' => 'View all stock transfers',
        ],
        'create stock transfer' => [
            'title' => 'Create Stock Transfer',
            'description' => 'Create new stock transfers',
        ],
        'edit stock transfer' => [
            'title' => 'Edit Stock Transfer',
            'description' => 'Edit draft stock transfers',
        ],
        'delete stock transfer' => [
            'title' => 'Delete Stock Transfer',
            'description' => 'Delete draft stock transfers',
        ],
        'approve stock transfer' => [
            'title' => 'Approve Stock Transfer',
            'description' => 'Approve pending stock transfers',
        ],
        'ship stock transfer' => [
            'title' => 'Ship Stock Transfer',
            'description' => 'Mark stock transfers as shipped',
        ],
        'receive stock transfer' => [
            'title' => 'Receive Stock Transfer',
            'description' => 'Receive stock transfers at destination',
        ],
        'cancel stock transfer' => [
            'title' => 'Cancel Stock Transfer',
            'description' => 'Cancel stock transfers',
        ],
    ],
];
