<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\PaymentMethods;
use App\Models\Product;
use App\Models\Role;
use App\Models\Vendor;
use Illuminate\Http\Request;

class Select2Controller extends Controller
{
  
  public function roles( Request $request ) : array
  {
    // $this->authorize('isAdmin');
    $model = Role::query();
    if( !empty( $request->term ) ) {
      $model->where( 'name', 'like', '%' . $request->term . '%' );
    }
    $model = $model->orderBy( 'name' )->get();
    $data = [];
    foreach( $model as $key => $value ) {
      $data[ $key ] = [
        'id'   => $value->id,
        'text' => $value->name,
      ];
      # code...
    }
    
    return [ 'results' => $data ];
  }
  
  public function paymentMethods( Request $request ) : array
  {
    // $this->authorize('isAdmin');
    $model = PaymentMethods::query();
    $model = $model->distinct( 'source' )->orderBy( 'source' )->get();
    $data = [];
    foreach( $model as $key => $value ) {
      $data[ $key ] = [
        'id'   => $value->id,
        'text' => $value->source,
      ];
      # code...
    }
    
    return [ 'results' => $data ];
  }
  
  public function banks( Request $request ) : array
  {
    // $this->authorize('isAdmin');
    $model = Bank::query();
    $model = $model->orderBy( 'name' )->get();
    $data = [];
    foreach( $model as $key => $value ) {
      $data[ $key ] = [
        'id'   => $value->id,
        'text' => $value->name,
      ];
      
      # code...
    }
    $data[] = [
      'id'   => 0,
      'text' => 'Cash'
    ];
    return [ 'results' => $data ];
  }
  
  public function expense( Request $request ) : array
  {
    // $this->authorize('isAdmin');
    $model = Expense::query();
    $model = $model->orderBy( 'name' )->get();
    $data = [];
    foreach( $model as $key => $value ) {
      $data[ $key ] = [
        'id'   => $value->id,
        'text' => $value->name,
      ];
      # code...
    }
    
    return [ 'results' => $data ];
  }
  
  public function vendorPayment( $id ) : array
  {
    // $this->authorize('isAdmin');
    $model = PaymentMethods::where( 'paymentable_id', trim( $id ) )
                           ->where( 'paymentable_type', Vendor::class );
    $model = $model->distinct( 'source' )->orderBy( 'name' )->get();
    $data = [];
    foreach( $model as $key => $value ) {
      $data[ $key ] = [
        'id'   => $value->id,
        'text' => $value->source . ' - ' . $value->name . ' - ' . $value->account_number,
      ];
      # code...
    }
    
    return [ 'results' => $data ];
  }
  
  public function customerPayment( $id ) : array
  {
    // $this->authorize('isAdmin');
    $model = PaymentMethods::where( 'paymentable_id', trim( $id ) )
                           ->where( 'paymentable_type', Customer::class );
    $model = $model->distinct( 'source' )->orderBy( 'name' )->get();
    $data = [];
    foreach( $model as $key => $value ) {
      $data[ $key ] = [
        'id'   => $value->id,
        'text' => $value->source . ' - ' . $value->name . ' - ' . $value->account_number,
      ];
      # code...
    }
    
    return [ 'results' => $data ];
  }
  
  public function categories( Request $request ) : array
  {
    // $this->authorize('isAdmin');
    $model = Category::query();
    if( !empty( $request->term ) ) {
      $model->where( 'name', 'like', '%' . $request->term . '%' );
    }
    $model = $model->orderBy( 'name' )->get();
    $data = [];
    foreach( $model as $key => $value ) {
      $data[ $key ] = [
        'id'   => $value->id,
        'text' => $value->name,
      ];
      # code...
    }
    
    return [ 'results' => $data ];
  }
  
  public function products( Request $request ) : array
  {
    // $this->authorize('isAdmin');
    $model = Product::query();
    if( !empty( $request->term ) ) {
      $model->where( 'name', 'like', '%' . $request->term . '%' );
      $model->orWhere( 'arabic_name', 'like', '%' . $request->term . '%' );
    }
    $model = $model->orderBy( 'name', 'ASC' )->limit( 20 )->get();
    $data = [];
    foreach( $model as $key => $value ) {
      $name = $value->arabic_name ? $value->full_name . ' - ' . $value->arabic_name : $value->full_name;
      $data[ $key ] = [
        'id'   => $value->id,
        'text' => $name,
      ];
      # code...
    }
    
    return [ 'results' => $data ];
  }
  
  public function customers( Request $request ) : array
  {
    // $this->authorize('isAdmin');
    $model = Customer::query();
    if( !empty( $request->term ) ) {
      $model->where( 'first_name', 'like', '%' . $request->term . '%' )
            ->orWhere( 'last_name', 'like', '%' . $request->term . '%' )->
        orWhere( 'address', 'like', '%' . $request->term . '%' );
    }
    $model = $model->orderBy( 'first_name' )->get();
    $data = [];
    foreach( $model as $key => $value ) {
      $data[ $key ] = [
        'id'   => $value->id,
        'text' => ucwords( $value->full_name ) . ' - ' . ucwords( $value->address ),
      ];
      # code...
    }
    
    return [ 'results' => $data ];
  }
  
  public function vendors( Request $request ) : array
  {
    // $this->authorize('isAdmin');
    $model = Vendor::query();
    if( !empty( $request->term ) ) {
      $model->where( 'name', 'like', '%' . $request->term . '%' );
    }
    $model = $model->orderBy( 'name' )->get();
    $data = [];
    foreach( $model as $key => $value ) {
      $data[ $key ] = [
        'id'   => $value->id,
        'text' => $value->name,
      ];
      # code...
    }
    
    return [ 'results' => $data ];
  }
  
}
