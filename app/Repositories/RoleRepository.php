<?php

namespace App\Repositories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Prettus\Repository\Eloquent\BaseRepository;

class RoleRepository extends BaseRepository
{
  
  public function model()
  {
    return Role::class;
  }
  
  /**
   * @return Builder
   */
  public function dataTablesQuery()
  {
    return Role::query();
  }
  
}
