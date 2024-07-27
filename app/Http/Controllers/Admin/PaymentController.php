<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PaymentMethods;
use App\Models\Vendor;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class PaymentController extends Controller
{
  
  /**
   * @param Customer $customer
   * @return Application|Factory|View
   */
  public function index( Customer $customer )
  {
    $payments = PaymentMethods::where( 'paymentable_id', $customer->id )
                              ->where( 'paymentable_type', Customer::class )->get();
    return view( 'admin.payments.index', [
      'payments'  => $payments,
      'full_name' => $customer->full_name,
      'id'        => $customer->id,
      'model'     => 'customer'
    ] );
  }// index
  
  public function vendorIndex( Vendor $vendor )
  {
    $payments = PaymentMethods::where( 'paymentable_id', $vendor->id )
                              ->where( 'paymentable_type', Vendor::class )->get();
    return view( 'admin.payments.index', [
      'payments' => $payments,
      'name'     => $vendor->name,
      'id'       => $vendor->id,
      'model'    => 'vendor'
    ] );
  }// index
  
}
