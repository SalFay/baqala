<?php

namespace App\Enums;

/**
 * Class InventoryStatus
 * @package App\Enums
 */
class InventoryStatus
{
  /**
   *
   */
  public const AVAILABLE = 'Available';
  
  /**
   *
   */
  public const SOLD = 'Sold';
  
  /**
   *
   */
  public const VENDOR_RETURNED = 'Returned Vendor';
  
  /**
   *
   */
  public const ORDER_RETURNED = 'Returned Order';
}// InventoryStatus
