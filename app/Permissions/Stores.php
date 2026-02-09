<?php

return [
    'title' => 'Stores',
    'description' => 'Manage Multi-Store Permissions',
    'permissions' => [
        'access stores' => [
            'title' => 'View Stores',
            'description' => 'View all stores list',
        ],
        'add store' => [
            'title' => 'Add Store',
            'description' => 'Create new store',
        ],
        'edit store' => [
            'title' => 'Edit Store',
            'description' => 'Edit store details',
        ],
        'delete store' => [
            'title' => 'Delete Store',
            'description' => 'Delete stores',
        ],
        'manage store users' => [
            'title' => 'Manage Store Users',
            'description' => 'Assign/remove users from stores',
        ],
        'access all stores data' => [
            'title' => 'Access All Stores Data',
            'description' => 'View data from all stores (not just assigned)',
        ],
        'transfer between stores' => [
            'title' => 'Transfer Between Stores',
            'description' => 'Create stock transfers between stores',
        ],
    ],
];
