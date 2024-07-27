<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Stock;
use App\Models\Vendor;
use App\Services\CartService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StockController extends Controller
{
  
  private $cartService;
  
  public function __construct( CartService $cartService )
  {
    $this->cartService = $cartService;
    $this->cartService->setForVendor();
  }
  
  /**
   * @param Vendor $vendor
   * @return Application|Factory|View
   */
  public function index( Vendor $vendor )
  {
    return view( 'admin.stock.index', [ 'vendor' => $vendor ] );
  }
  
  public function invoice( Stock $order )
  {
    return view( 'admin.inventory.invoice', [ 'order' => $order ] );
  }
  
  /**
   * @param Vendor $vendor
   * @return Application|Factory|View
   */
  public function showCart( Vendor $vendor )
  {
    $this->cartService->setCustomerId( $vendor->id );
    $cart = $this->cartService->getContent();
    return \view( 'admin.inventory.cart', [ 'vendor' => $vendor, 'cart' => $cart ] );
  }// showCart
  
  public function addToCart( Request $request, Vendor $vendor )
  {
    $this->cartService->setCustomerId( $vendor->id );
    
    $id = $request->id;
    $stock = $request->stock;
    $price = $request->purchase_price;
    if( isset( $request->price ) ) {
      // Update Cart from Cart Data onChange
      $this->cartService->updateWithPrice( $id, $stock, $request->price );
    } else {
      //Update Cart from Products Table on addToCart
      $this->cartService->add( $id, $stock, $price );
    }
    
    return response()->json( [ 'status' => 'ok', 'message' => 'Stock Added to Cart' ], 200 );
    
  }// addToCart
  
  public function deleteFromCart( Request $request, Vendor $vendor )
  {
    $this->cartService->setCustomerId( $vendor->id );
    $this->cartService->remove( $request->id );
    return response()->json( [ 'status' => 'ok', 'message' => 'Product Deleted from Cart' ], 200 );
    
  }
  
  public function emptyCart( Vendor $vendor )
  {
    $this->cartService->setCustomerId( $vendor->id );
    $this->cartService->trash();
    return response()->json( [ 'status' => 'ok', 'message' => 'Cart Deleted Successfully' ], 200 );
    
  }
  
  public function store( Request $request, Vendor $vendor )
  {
    
    $paid = $request->amount;
    $total = $request->total;
    $stockData = [
      'vendor_id'        => $vendor->id,
      'payment_type'     => 'Simple',
      'date'             => $request->date,
      'discount'         => $request->discount,
      'delivery_charges' => $request->delivery_charges,
      'sub_total'        => $request->sub_total,
      'total'            => $total,
      'credit'           => $paid,
      'debit'            => $total - $paid,
      'invoice_no'       => $request->invoice_no
    ];
    
    $stock = Stock::create( $stockData );
    
    foreach( $request->products as $product ) {
      for( $i = 1; $i <= $product[ 'stock' ]; $i++ ) {
        $prod = Inventory::where( 'product_id', $product[ 'id' ] )
                         ->where( 'status', 'Sold' )->where( 'stock_id', 0 )->first();
        if( $prod ) {
          $prod->update( [
            'stock_id' => $stock->id
          ] );
        } else {
          Inventory::create( [
            'stock_id'   => $stock->id,
            'product_id' => $product[ 'id' ],
            'cost'       => $product[ 'pprice' ],
            'status'     => 'Available',
            'date'       => $request->date
          ] );
        }
        
      }
      
      InventoryLog::create( [
        'order_type' => Stock::class,
        'order_id'   => $stock->id,
        'product_id' => $product[ 'id' ],
        'stock'      => $product[ 'stock' ],
        'cost'       => $product[ 'pprice' ],
        'status'     => 'Available',
        'date'       => $request->date
      ] );
      
    }
    
    // if( $request->payment_type === 'Simple' ) {
    
    //$remaining = $total - $paid;
    /*  if( $remaining > 0 ) {
          if( $paid > 0 ) {
              Payment::create( [
                  'order_type'        => Stock::class,
                  'order_id'          => $stock->id,
                  'amount'            => $paid,
                  'payment_method_id' => null,
                  'scheduled_on'      => date( 'Y-m-d' ),
                  'completed_on'      => date( 'Y-m-d' ),
                  'status'            => 'Completed',
              ] );
          }
          Payment::create( [
              'order_type'        => Stock::class,
              'order_id'          => $stock->id,
              'amount'            => $remaining,
              'payment_method_id' => null,
              'scheduled_on'      => Carbon::now()->addDays( 7 ),
              'completed_on'      => Carbon::now()->addDays( 7 ),
              'status'            => 'Scheduled',
          ] );
      } else {*/
    
    Account::create( [
      'party_type' => Vendor::class,
      'party_id'   => $vendor->id,
      'credit'     => $total + $request->discount + $request->delivery_charges,
      'comments'   => 'Stock No.' . $stock->id,
    ] );
    Account::create( [
      'party_type' => Vendor::class,
      'party_id'   => $vendor->id,
      'debit'      => $request->discount + $request->delivery_charges,
      'comments'   => 'Stock No.' . $stock->id,
    ] );
    
    if( $paid > 0 ) {
      Account::create( [
        'party_type' => Vendor::class,
        'party_id'   => $vendor->id,
        'debit'      => $paid,
        'bank_id'    => 2,
        'comments'   => 'Stock No.' . $stock->id,
      ] );
    }
    /*    }
  } else {
       $installment = $request->installment;
       for( $i = 1; $i <= $installment; $i++ ) {
           $sche_date = $request->scheduled_on;
           if( $i > 1 ) {
               $date = new Carbon( $sche_date );
               $sche_date = $date->addDays( 7 * $i );
           }
           Payment::create( [
               'order_type'        => Stock::class,
               'order_id'          => $stock->id,
               'amount'            => $paid,
               'payment_method_id' => $request->payment_method_id,
               'scheduled_on'      => $sche_date,
               'completed_on'      => $sche_date,
               'status'            => 'Scheduled',
           ] );
       }

   }*/
    
    $this->cartService->setCustomerId( $vendor->id );
    $this->cartService->trash();
    return response()->json( [ 'status' => 'ok', 'message' => 'Stock Added Successfully', 'url' => url( 'admin/inventory/invoice/' . $stock->id ) ],
      200 );
    
  }
  
}
