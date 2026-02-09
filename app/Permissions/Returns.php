<?php

return [
    'title' => 'Returns & Exchanges',
    'description' => 'Manage Returns and Exchanges Permissions',
    'permissions' => [
        'access returns' => [
            'title' => 'View Returns',
            'description' => 'View all return requests',
        ],
        'create return' => [
            'title' => 'Create Return',
            'description' => 'Create new return request',
        ],
        'approve return' => [
            'title' => 'Approve Return',
            'description' => 'Approve pending return requests',
        ],
        'reject return' => [
            'title' => 'Reject Return',
            'description' => 'Reject pending return requests',
        ],
        'process return' => [
            'title' => 'Process Return',
            'description' => 'Process approved returns (refund/exchange)',
        ],
        'view return reports' => [
            'title' => 'View Return Reports',
            'description' => 'Access return statistics and reports',
        ],
    ],
];
