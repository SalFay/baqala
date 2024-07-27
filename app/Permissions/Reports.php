<?php

return [
  'title'       => 'Reports',
  'description' => 'Manage Reports Permissions',
  'permissions' => [
    'access reports'               => [
      'title'       => 'Show Report Menu',
      'description' => 'see all Reports',
    ],
    'access stocks report'         => [
      'title'       => 'Show Stocks Report',
      'description' => 'see all available stock, sold stocks',
    ],
    'access orders report'         => [
      'title'       => 'Show Orders Report',
      'description' => 'see all orders invoices details',
    ],
    'access profit report'         => [
      'title'       => 'Show Profit / Margin Report',
      'description' => 'see all products / orders profit / margin',
    ],
    'access stock invoices report' => [
      'title'       => 'Show Stock Invoices Report',
      'description' => 'see all vendor invoices',
    ], 'access inventory report'   => [
      'title'       => 'Show Inventory Report',
      'description' => 'see all inventory logs i.e Available Item / Sold Items',
    ],
  
  ],
];
