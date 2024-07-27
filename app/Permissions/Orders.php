<?php

return [
  'title'       => 'Orders',
  'description' => 'Manage Orders Permissions',
  'permissions' => [
    'add order' => [
      'title'       => 'Add Order',
      'description' => 'Add new Order to the system',
    ],
    
    'access customer invoice' => [
      'title'       => 'View Customer Invoice',
      'description' => 'View customer invoice details',
    ],
    
    'show order cart' => [
      'title'       => 'Show Cart',
      'description' => 'show order cart details',
    ],
    
    'add to order cart'      => [
      'title'       => 'Add to Cart',
      'description' => 'Add Items to Order Cart',
    ],
    'remove from order cart' => [
      'title'       => 'Remove Item',
      'description' => 'Remove Item from Order Cart'
    ],
    'empty order cart'       => [
      'title'       => 'Empty Cart',
      'description' => 'empty order cart'
    ],
  
  ],
];
