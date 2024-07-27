<?php

namespace App\Repositories;

use App\Models\CustomerCredit;
use App\Models\VendorCredit;
use Illuminate\Database\Eloquent\Builder;
use Prettus\Repository\Eloquent\BaseRepository;

class VendorCreditRepository extends BaseRepository
{
  
  public function model()
  {
    return VendorCredit::class;
  }
  
  /**
   * @return Builder
   */
  public function dataTablesQuery()
  {
    return VendorCredit::query();
  }
  
}
