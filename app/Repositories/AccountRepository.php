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
  public const TYPES = [
    'customer' => \App\Models\Customer::class,
    'vendor'   => \App\Models\Vendor::class,
    'expense'  => \App\Models\Expense::class,
    'bank'     => \App\Models\Bank::class,
  ];

  /**
   * @param string $type   customer | vendor | expense | bank | '' (all)
   * @param string $source sales (posted by the POS for each order) | manual (everything else) | '' (all)
   */
  public function dataTablesQuery( $start, $end, string $type = '', string $source = '' )
  {
    $query = Account::query();
    if( $start && $end ) {
      $query->where( 'created_at', '>=', $start )->where( 'created_at', '<=', $end );
    }
    if( isset( self::TYPES[ $type ] ) ) {
      $query->where( 'party_type', self::TYPES[ $type ] );
    }
    if( $source === 'sales' ) {
      $query->where( 'comments', 'LIKE', 'Order No.%' );
    } elseif( $source === 'manual' ) {
      $query->where( fn( $q ) => $q->whereNull( 'comments' )->orWhere( 'comments', 'NOT LIKE', 'Order No.%' ) );
    }
    return $query->orderBy( 'id', 'desc' );
  }
  
}
