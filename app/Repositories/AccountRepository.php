<?php

namespace App\Repositories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Builder;
use Prettus\Repository\Eloquent\BaseRepository;

class AccountRepository extends BaseRepository
{
  
  public function model()
  {
    return Account::class;
  }
  
  /**
   * @return Builder
   */
  public function dataTablesQuery( $start, $end )
  {
    
    $query = Account::query();
    if( $start && $end ) {
      $query->where( 'created_at', '>=', $start )->where( 'created_at', '<=', $end );
    }
    return $query->orderBy( 'id', 'desc' )->limit( 100 );
  }
  
}
