<?php

return [
    'title' => 'Settings',
    'description' => 'Manage System Settings Permissions',
    'permissions' => [
        'access settings' => [
            'title' => 'View Settings',
            'description' => 'View system settings',
        ],
        'edit general settings' => [
            'title' => 'Edit General Settings',
            'description' => 'Edit general store settings (name, address, etc.)',
        ],
        'edit tax settings' => [
            'title' => 'Edit Tax Settings',
            'description' => 'Edit tax rates and tax configuration',
        ],
        'edit receipt settings' => [
            'title' => 'Edit Receipt Settings',
            'description' => 'Edit receipt template and settings',
        ],
        'edit pos settings' => [
            'title' => 'Edit POS Settings',
            'description' => 'Edit POS terminal settings',
        ],
        'upload logo' => [
            'title' => 'Upload Logo',
            'description' => 'Upload store logo',
        ],
    ],
];
