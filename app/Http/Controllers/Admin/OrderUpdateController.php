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
use Illuminate\Support\Facades\DB;

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
    Order::normalizeCartCosts( $request );
    $paid = $request->amount;
    $total = $request->total;
    $type = $request->payment_type;
    $data = [
      'payment_type'     => $type,
      'date'             => $request->date,
      'discount'         => $request->discount,
      'discount_type'    => $request->input( 'discount_type', $order->discount_type ),
      'vat'              => option( 'vat_amount' ),
      'delivery_charges' => $request->delivery_charges,
      'sub_total'        => $request->sub_total,
      'credit'           => $paid,
      'debit'            => $total - $paid,
      'total'            => $total,
      'customer_name'    => $request->customer_name,
      'cashier_name'     => $request->cashier_name
    ];
    
    // All or nothing, and the ledger is replaced rather than added to: posting the full
    // edited total again on every edit counted the sale twice (Rs 321,278 on live).
    DB::transaction( function() use ( $request, $order, $data, $total, $paid ) {
      $order->update( $data );
    
      // Lines removed in the edit cart: return their units and drop the line. (The cart
      // used to delete the line on click, before Save, and never returned its units.)
      $keep = collect( $request->products )->pluck( 'id' )->all();
      foreach( $order->items()->whereNotIn( 'product_id', $keep )->get() as $item ) {
        Inventory::release( $order->id, $item->product_id, $item->stock );
        $this->logStock( $order, $item->product_id, $item->stock, $item->sale_price, 0, 'Available', $request->date );
        $item->delete();
      }

      foreach( $request->products as $product ) {
        $item = OrderItem::where( 'order_type', Order::class )->where( 'order_id', $order->id )
                         ->where( 'product_id', $product[ 'id' ] )->first();
        // The order line holds the quantity. Counting stock rows instead made untouched
        // lines grow on save for old orders holding fewer rows than their quantity.
        $diff = (int) $product[ 'stock' ] - (int) ( $item->stock ?? 0 );
        $taxable = round( $product[ 'taxable_price' ] ?? 0, 2 );

        if( $diff > 0 ) {
          Inventory::sell( $order->id, $product, $diff, $request->date );
          $this->logStock( $order, $product[ 'id' ], $diff, $product[ 'sale_price' ], $taxable, 'Sold', $request->date );
        } elseif( $diff < 0 ) {
          // Only this order's units (it used to free the first sold unit of any order).
          Inventory::release( $order->id, $product[ 'id' ], -$diff );
          $this->logStock( $order, $product[ 'id' ], -$diff, $product[ 'sale_price' ], 0, 'Available', $request->date );
        }

        if( !$item ) {
          OrderItem::create( [
            'order_type'     => Order::class,
            'order_id'       => $order->id,
            'product_id'     => $product[ 'id' ],
            'stock'          => $product[ 'stock' ],
            'purchase_price' => $product[ 'purchase_price' ],
            'sale_price'     => $product[ 'sale_price' ],
            'taxable_price'  => $taxable,
            'status'         => 'Delivered',
            'date'           => $request->date
          ] );
        } elseif( $product[ 'stock' ] <= 0 ) {
          $item->delete();
        } elseif( $diff !== 0 ) {
          $item->update( [ 'stock' => $product[ 'stock' ] ] );
        }
      }

      $bank = Account::forOrder( $order->id )->where( 'credit', '>', 0 )->value( 'bank_id' ) ?? 0;
      Account::forOrder( $order->id )->delete();
      Account::postOrder( $order, $total, $paid, $bank );
    } );
    
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
  
  private function logStock( Order $order, $productId, $qty, $price, $taxable, string $status, $date ) : void
  {
    InventoryLog::create( [
      'order_type'    => Order::class,
      'order_id'      => $order->id,
      'product_id'    => $productId,
      'stock'         => $qty,
      'cost'          => $price,
      'taxable_price' => $taxable,
      'status'        => $status,
      'date'          => $date
    ] );
  }

  /**
   * Remove a line from the edit cart only; the order changes when the edit is saved.
   */
  public function deleteFromCart( Request $request, Order $order )
  {
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
