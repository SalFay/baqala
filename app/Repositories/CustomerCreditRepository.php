<?php

namespace App\Repositories;

use App\Models\CustomerCredit;
use Illuminate\Database\Eloquent\Builder;
use Prettus\Repository\Eloquent\BaseRepository;

class CustomerCreditRepository extends BaseRepository
{
  
  public function model()
  {
    return CustomerCredit::class;
  }
  
  /**
   * @return Builder
   */
  public function dataTablesQuery()
  {
    return CustomerCredit::query();
  }
  
}
