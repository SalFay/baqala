<?php

use App\Models\Account;
use App\Models\Bank;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Vendor;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Illuminate\Support\Str;

function format_date( $format = 'm/d/Y', $date = 'now' )
{
  $tz = new CarbonTimeZone( config( 'app.timezone' ) );
  $carbon = new Carbon( $date, $tz );
  return $carbon->format( $format );
}

/**
 * @param $id
 * @return int
 */
function StockChecking( $id, $stock )
{
  $stockCount = Inventory::where( 'product_id', $id )
                         ->where( 'status', 'Available' )->count();
  if( $stockCount !== null ) {
    if( $stockCount < $stock ) {
      return 0;
    } else {
      return $stockCount;
    }
  }
  
}// stock_count

function checkingBalance( $customer )
{
  $statement = Account::where( 'party_type', Customer::class )
                      ->where( 'party_id', $customer )->get();
  
  if( $statement ) {
    $total = 0;
    foreach( $statement as $st ) {
      $total += $st->debit - $st->credit;
    }
    return $total;
  }
}

function checkingBalanceVendor( $vendor )
{
  $statement = Account::where( 'party_type', Vendor::class )
                      ->where( 'party_id', $vendor )->get();
  
  if( $statement ) {
    $total = 0;
    foreach( $statement as $st ) {
      $total += $st->credit - $st->debit;
    }
    return $total;
  }
}

function calculateProductVat( $amount, $id, $stock )
{
  $product = Product::where( 'id', $id )->first();
  if( $product->taxable === 'Yes' ) {
    return round( $stock * $amount * option( 'vat_amount' ) / 100, 2 );
    
  }
  return 0;
}

function calculateVat( $amount ) : float
{
  return round( $amount * option( 'vat_amount' ) / 100, 2 );
}

/**
 * @param $id
 * @return mixed
 */
function stockSold( $id )
{
  return Inventory::where( 'product_id', $id )
                  ->where( 'status', 'Sold' )->count();
  
}// stock_count

function stockReturn( $id )
{
  return Inventory::where( 'product_id', $id )
                  ->where( 'status', 'Returned Order' )->count();
  
}// stock_count

function totalStock( $id )
{
  return Inventory::where( 'product_id', $id )->count();
  
}// stock_count

function addBank( $name )
{
  if( empty( $name ) || ctype_digit( $name ) ) {
    return $name;
  }
  $bank = Bank::where( 'name', $name )
              ->first();
  if( empty( $bank ) ) {
    $bank = Bank::create( [
      'name' => Str::title( $name ),
    ] );
  }
  return $bank->id;
}

function addCategory( $name )
{
  if( empty( $name ) || ctype_digit( $name ) ) {
    return $name;
  }
  $category = Category::where( 'name', $name )
                      ->first();
  if( empty( $category ) ) {
    $category = Category::create( [
      'name' => Str::title( $name ),
      'code' => Str::title( $name ),
    ] );
  }
  return $category->id;
}

function addExpense( $name )
{
  if( empty( $name ) || ctype_digit( $name ) ) {
    return $name;
  }
  $expense = Expense::where( 'name', $name )
                    ->first();
  if( empty( $expense ) ) {
    $expense = Expense::create( [
      'name' => Str::title( $name ),
    ] );
  }
  return $expense->id;
}

function name( $type, $id )
{
  $name = '';
  if( $type === 'Customer' ) {
    $customer = Customer::where( 'id', $id )->first();
    $name = $customer->full_name . ' ' . $customer->address;
  }
  
  if( $type === 'Vendor' ) {
    $vendor = Vendor::where( 'id', $id )->first();
    $name = $vendor->name;
  }
  
  if( $type === 'Bank' ) {
    $bank = Bank::where( 'id', $id )->first();
    $name = $bank->name;
  }
  
  if( $type === 'Expense' ) {
    $expense = Expense::where( 'id', $id )->first();
    $name = $expense->name;
  }
  
  return $name;
}

function averageCost( $id )
{
  $stock = Inventory::where( 'product_id', $id )
                    ->where( 'status', 'Available' )->count();
  $amount = Inventory::where( 'product_id', $id )
                     ->where( 'status', 'Available' )->sum( 'cost' );
  $cost = 0;
  if( $amount > 0 ) {
    $cost = round( $amount / $stock, 2 );
  } else {
    $cost = Product::where( 'id', $id )->first()->purchase_price;
  }
  
  return $cost;
}// stock_count

function convert_object_to_array( $data )
{
  
  if( is_object( $data ) ) {
    $data = get_object_vars( $data );
  }
  
  if( is_array( $data ) ) {
    return array_map( __FUNCTION__, $data );
  } else {
    return $data;
  }
}

function logCauserDetail( $subjectId )
{
  $user = \App\Models\User::find( $subjectId );
  if( $user ) {
    return $user->first_name . ' ' . $user->last_name;
  }
  return '';
}

function startDate( $date )
{
  $start = '';
  if( $date ) {
    $start = explode( 'to', $date )[ 0 ];
    return Carbon::make( $start )->format( 'Y-m-d 00:00:00' );
  }
  return '';
}

function endDate( $date )
{
  if( $date ) {
    $end = explode( 'to', $date )[ 1 ];
    return Carbon::make( $end )->format( 'Y-m-d 23:59:59' );
  }
  
  return '';
}


