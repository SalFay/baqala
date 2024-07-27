<?php

return [
  'title'       => 'General',
  'description' => 'Manage General Permissions',
  'permissions' => [
    'access dashboard' => [
      'title'       => 'View Dashboard',
      'description' => 'See all graphs and data of dashboard',
    ],
    
    'show pos' => [
      'title'       => 'Access POS',
      'description' => 'Access to POS for order',
    ],
    
    'access settings' => [
      'title'       => 'Manage Role / General Settings',
      'description' => 'Change Roles / General settings'
    ],
    
    'access invoices' => [
      'title'       => 'Manage Invoices',
      'description' => 'List of all Customer / Vendor Invoices'
    ]
  
  ],
];
