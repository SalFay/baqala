<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Prettus\Repository\Eloquent\BaseRepository;

class UserRepository extends BaseRepository
{
  
  public function model()
  {
    return User::class;
  }
  
  /**
   * @return Builder
   */
  public function dataTablesQuery()
  {
    return User::query();
  }
  
}
