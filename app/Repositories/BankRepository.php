<?php

namespace App\Repositories;

use App\Models\Bank;
use Illuminate\Database\Eloquent\Builder;
use Prettus\Repository\Eloquent\BaseRepository;

class BankRepository extends BaseRepository
{
  
  public function model()
  {
    return Bank::class;
  }
  
  /**
   * @return Builder
   */
  public function dataTablesQuery()
  {
    return Bank::query();
  }
  
}
