<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Prettus\Repository\Eloquent\BaseRepository;

class CategoryRepository extends BaseRepository
{
  
  public function model()
  {
    return Category::class;
  }
  
  /**
   * @return Builder
   */
  public function dataTablesQuery()
  {
    return Category::query();
  }
  
}
