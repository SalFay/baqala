<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OrderUpdateController extends Controller
{
  
  private $cartService;
  
  public function __construct( CartService $cartService )
  {
    $this->cartService = $cartService;
    $this->cartService->setForOrder();
  }
  
  public function edit( Order $order )
  {
    $this->cartService->setCustomerId( $order->id );
    
    foreach( $order->items as $product ) {
      $cart = $this->cartService->getCart();
      if( !isset( $cart[ $product->product_id ] ) ) {
        $taxable_price = $product->taxable_price != 0 ? $product->taxable_price : $product->sale_price;
        $this->cartService->add( $product->product_id, $product->stock, $product->sale_price,
          [ 'purchase_price' => $product->purchase_price, 'taxable_price' => $taxable_price ] );
        
      }
    }
    return view( 'admin.order.edit', [ 'order' => $order ] );
  }
  
  /**
   * @param Order $order
   * @return Application|Factory|View
   */
  public function showCart( Order $order )
  {
    $this->cartService->setCustomerId( $order->id );
    $cart = $this->cartService->getContent();
    return \view( 'admin.order.cart', [ 'order' => $order, 'cart' => $cart ] );
  }// showCart
  
  public function update( Request $request, Order $order )
  {
    $paid = $request->amount;
    $total = $request->total;
    $type = $request->payment_type;
    $data = [
      'payment_type'     => $type,
      'date'             => $request->date,
      'discount'         => $request->discount,
      'vat'              => option( 'vat_amount' ),
      'delivery_charges' => $request->delivery_charges,
      'sub_total'        => $request->sub_total,
      'credit'           => $paid,
      'debit'            => $total - $paid,
      'total'            => $total,
      'customer_name'    => $request->customer_name,
      'cashier_name'     => $request->cashier_name
    ];
    
    $order->update( $data );
    
    foreach( $request->products as $product ) {
      
      $count = Inventory::where( 'product_id', $product[ 'id' ] )
                        ->where( 'order_id', $order->id )->where( 'status', 'Sold' )->count();
      $orderItem = OrderItem::where( 'order_id', $order->id )
                            ->where( 'product_id', $product[ 'id' ] )->first();
      if( $orderItem ) {
        if( $count > $product[ 'stock' ] ) {
          $stock = $count - $product[ 'stock' ];
          if( $product[ 'stock' ] <= 0 ) {
            $orderItem->delete();
          } else {
            $orderItem->update( [
              'stock' => $orderItem->stock - $stock,
            ] );
          }
          
          InventoryLog::create( [
            'order_type'    => Order::class,
            'order_id'      => $order->id,
            'product_id'    => $product[ 'id' ],
            'stock'         => $stock,
            'cost'          => $product[ 'sale_price' ],
            'taxable_price' => 0,
            'status'        => 'Available',
            'date'          => now()
          ] );
          for( $i = 1; $i <= $stock; $i++ ) {
            $prod = Inventory::where( 'product_id', $product[ 'id' ] )
                             ->where( 'status', 'Sold' )->first();
            if( $prod ) {
              $prod->update( [
                'status'   => 'Available',
                'order_id' => $order->id
              ] );
            } else {
              Inventory::create( [
                'stock_id'   => 0,
                'order_id'   => $order->id,
                'product_id' => $product[ 'id' ],
                'cost'       => $product[ 'purchase_price' ],
                'status'     => 'Available',
                'date'       => $request->date
              ] );
            }
            
          }
        }
        if( $count < $product[ 'stock' ] ) {
          $stock = $product[ 'stock' ] - $count;
          $orderItem->update( [
            'stock' => $orderItem->stock + $stock,
          ] );
          InventoryLog::create( [
            'order_type'    => Order::class,
            'order_id'      => $order->id,
            'product_id'    => $product[ 'id' ],
            'stock'         => $stock,
            'cost'          => $product[ 'sale_price' ],
            'taxable_price' => 0,
            'status'        => 'Sold',
            'date'          => now()
          ] );
          for( $i = 1; $i <= $stock; $i++ ) {
            $prod = Inventory::where( 'product_id', $product[ 'id' ] )
                             ->where( 'status', 'Available' )->first();
            if( $prod ) {
              $prod->update( [
                'order_id' => $order->id,
                'status'   => 'Sold'
              ] );
            } else {
              Inventory::create( [
                'stock_id'   => 0,
                'order_id'   => $order->id,
                'product_id' => $product[ 'id' ],
                'cost'       => $product[ 'purchase_price' ],
                'status'     => 'Sold',
                'date'       => $request->date
              ] );
            }
            
          }
        }
      } else {
        for( $i = 1; $i <= $product[ 'stock' ]; $i++ ) {
          
          $prod = Inventory::where( 'product_id', $product[ 'id' ] )
                           ->where( 'status', 'Available' )->first();
          if( $prod ) {
            $prod->update( [
              'status'   => 'Sold',
              'order_id' => $order->id
            ] );
          } else {
            Inventory::create( [
              'stock_id'   => 0,
              'order_id'   => $order->id,
              'product_id' => $product[ 'id' ],
              'cost'       => $product[ 'purchase_price' ],
              'status'     => 'Sold',
              'date'       => $request->date
            ] );
          }
          
        }
        OrderItem::create( [
          'order_type'     => Order::class,
          'order_id'       => $order->id,
          'product_id'     => $product[ 'id' ],
          'stock'          => $product[ 'stock' ],
          'purchase_price' => $product[ 'purchase_price' ],
          'sale_price'     => $product[ 'sale_price' ],
          'taxable_price'  => round( $product[ 'taxable_price' ], 2 ),
          'status'         => 'Delivered',
          'date'           => $request->date
        ] );
        
        InventoryLog::create( [
          'order_type'    => Order::class,
          'order_id'      => $order->id,
          'product_id'    => $product[ 'id' ],
          'stock'         => $product[ 'stock' ],
          'cost'          => $product[ 'sale_price' ],
          'taxable_price' => round( $product[ 'taxable_price' ], 2 ),
          'status'        => 'Sold',
          'date'          => $request->date
        ] );
      }
    }
    
    Account::create( [
      'party_type' => Customer::class,
      'party_id'   => $order->customer->id,
      'debit'      => $total,
      'bank_id'    => 0,
      'comments'   => 'Updated Order No.' . $order->id,
    ] );
    
    if( $paid > 0 ) {
      Account::create( [
        'party_type' => Customer::class,
        'party_id'   => $order->customer->id,
        'credit'     => $paid,
        'bank_id'    => 0,
        'comments'   => 'Updated Order No.' . $order->id,
      ] );
    }
    
    $this->cartService->setCustomerId( $order->id );
    $this->cartService->trash();
    
    return response()->json( [
      'status' => 'ok', 'message' => 'Order Updated Successfully', 'url' => route( 'order.invoice', $order->id )
    ] );
    
  }
  
  public function addToCart( Request $request, Order $order )
  {
    $this->cartService->setCustomerId( $order->id );
    
    $id = $request->id;
    $stock = $request->stock;
    $price = $request->sale_price;
    $pprice = $request->purchase_price;
    $taxable_price = $request->taxable_price;
    //if( StockChecking( $id, $stock ) !== 0 ) {
    if( isset( $request->price ) ) {
      $taxable_price = $taxable_price != 0 ? $taxable_price : $request->price;
      
      // Update Cart from Cart Data onChange
      $this->cartService->updateWithPrice( $id, $stock, $request->price,
        [ 'purchase_price' => $pprice, 'taxable_price' => $taxable_price ] );
    } else {
      //Update Cart from Products Table on addToCart
      $this->cartService->add( $id, $stock, $price,
        [ 'purchase_price' => $pprice, 'taxable_price' => $taxable_price ] );
    }
    return response()->json( [ 'status' => 'ok', 'message' => 'Stock Added to Cart' ], 200 );
    /*   } else {
           return response()->json( [ 'status' => 'error', 'message' => 'Stock Entry is Greater than Available Stock' ],
               400 );
       }*/
    
  }// addToCart
  
  public function deleteFromCart( Request $request, Order $order )
  {
    $item = OrderItem::where( 'product_id', $request->id )->where( 'order_id', $order->id )->first();
    if( $item ) {
      $item->delete();
    }
    $this->cartService->setCustomerId( $order->id );
    $this->cartService->remove( $request->id );
    return response()->json( [ 'status' => 'ok', 'message' => 'Product Deleted from Cart' ], 200 );
    
  }
  
  public function emptyCart( Order $order )
  {
    
    $this->cartService->setCustomerId( $order->id );
    $this->cartService->trash();
    //			OrderItem::where( 'order_id', $order->id )->delete();
    return response()->json( [ 'status' => 'ok', 'message' => 'Cart Deleted Successfully' ], 200 );
    
  }
  
}
