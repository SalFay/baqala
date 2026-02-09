<?php

return [
    'title' => 'Loyalty Program',
    'description' => 'Manage Loyalty Program Permissions',
    'permissions' => [
        'access loyalty' => [
            'title' => 'View Loyalty Program',
            'description' => 'View loyalty settings and tiers',
        ],
        'manage loyalty tiers' => [
            'title' => 'Manage Loyalty Tiers',
            'description' => 'Create, edit, delete loyalty tiers',
        ],
        'view customer loyalty' => [
            'title' => 'View Customer Loyalty',
            'description' => 'View customer loyalty points and history',
        ],
        'adjust loyalty points' => [
            'title' => 'Adjust Loyalty Points',
            'description' => 'Manually adjust customer loyalty points',
        ],
        'redeem loyalty points' => [
            'title' => 'Redeem Loyalty Points',
            'description' => 'Redeem loyalty points at checkout',
        ],
    ],
];
