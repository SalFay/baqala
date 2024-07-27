<?php

namespace App\Repositories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Prettus\Repository\Eloquent\BaseRepository;

class VendorRepository extends BaseRepository
{
  
  public function model()
  {
    return Vendor::class;
  }
  
  /**
   * @return Builder
   */
  public function dataTablesQuery()
  {
    return Vendor::query();
  }
  
}
