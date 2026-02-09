<?php

return [
    'title' => 'Purchase Orders',
    'description' => 'Manage Purchase Order Permissions',
    'permissions' => [
        'access purchase orders' => [
            'title' => 'View Purchase Orders',
            'description' => 'View all purchase orders',
        ],
        'create purchase order' => [
            'title' => 'Create Purchase Order',
            'description' => 'Create new purchase orders',
        ],
        'edit purchase order' => [
            'title' => 'Edit Purchase Order',
            'description' => 'Edit draft purchase orders',
        ],
        'delete purchase order' => [
            'title' => 'Delete Purchase Order',
            'description' => 'Delete draft purchase orders',
        ],
        'submit purchase order' => [
            'title' => 'Submit Purchase Order',
            'description' => 'Submit purchase orders for approval',
        ],
        'approve purchase order' => [
            'title' => 'Approve Purchase Order',
            'description' => 'Approve pending purchase orders',
        ],
        'receive purchase order' => [
            'title' => 'Receive Purchase Order',
            'description' => 'Receive items from purchase orders',
        ],
        'cancel purchase order' => [
            'title' => 'Cancel Purchase Order',
            'description' => 'Cancel purchase orders',
        ],
    ],
];
