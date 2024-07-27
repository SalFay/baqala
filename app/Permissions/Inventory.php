<?php

return [
  'title'       => 'Inventory',
  'description' => 'Manage Inventory Permissions',
  'permissions' => [
    'add inventory' => [
      'title'       => 'Add Inventory',
      'description' => 'Add new Inventory to the system',
    ],
    
    'access vendor invoice' => [
      'title'       => 'View Vendor Invoice',
      'description' => 'View vendor invoice details',
    ],
    
    'show inventory cart' => [
      'title'       => 'Show Cart',
      'description' => 'show inventory cart details',
    ],
    
    'add to inventory cart'      => [
      'title'       => 'Add to Cart',
      'description' => 'Add Items to Inventory Cart',
    ],
    'remove from inventory cart' => [
      'title'       => 'Remove Item',
      'description' => 'Remove Item from Inventory Cart'
    ],
    'empty inventory cart'       => [
      'title'       => 'Empty Cart',
      'description' => 'empty inventory cart'
    ],
  
  ],
];
