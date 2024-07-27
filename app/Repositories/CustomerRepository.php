<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Prettus\Repository\Eloquent\BaseRepository;

class CustomerRepository extends BaseRepository
{
  
  public function model()
  {
    return Customer::class;
  }
  
  /**
   * @return Builder
   */
  public function dataTablesQuery()
  {
    return Customer::query();
  }
  
}
