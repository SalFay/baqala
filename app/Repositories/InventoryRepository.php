<?php

namespace App\Repositories;

use App\Models\Inventory;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Class InventoryRepository
 * @package App\Repositories
 */
class InventoryRepository extends BaseRepository
{
  
  /**
   * @return string
   */
  public function model()
  {
    return Inventory::class;
  }
}
