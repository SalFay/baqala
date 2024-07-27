<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Prettus\Repository\Eloquent\BaseRepository;

class ProductRepository extends BaseRepository
{
  
  public function model()
  {
    return Product::class;
  }
  
  /**
   * @return Builder
   */
  public function dataTablesQuery()
  {
    return Product::query();
  }
  
}
