<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Option;

class OrderController extends Controller
{

  private $cartService;

  public function __construct( CartService $cartService )
  {
    $this->cartService = $cartService;
    $this->cartService->setForClient();
  }

  /**
   * @param Customer $customer
   * @return Application|Factory|View
   */
  public function index( Customer $customer )
  {
    return view( 'admin.order.index', [ 'customer' => $customer ] );
  }

  public function invoice( Order $order )
  {
    if( option( 'printer' ) === 'Thermal Arabic' ) {
      return view( 'admin.order.thermal', [ 'order' => $order ] );

    } elseif( option( 'printer' ) === 'Thermal Arabic New' ) {
      return view( 'admin.order.thermal_new', [ 'order' => $order ] );

    } elseif( option( 'printer' ) === 'Thermal Eng' ) {
      return view( 'admin.order.thermal_en', [ 'order' => $order ] );

    } else {
      return view( 'admin.order.invoice', [ 'order' => $order ] );

    }

  }

  /**
   * @param Customer $customer
   * @return Application|Factory|View
   */
  public
  function showCart(
    Customer $customer
  ) {
    $this->cartService->setCustomerId( $customer->id );
    $cart = $this->cartService->getContent();
    return \view( 'admin.order.cart', [ 'customer' => $customer, 'cart' => $cart ] );
  }// showCart

  public function get( Request $request )
  {
    $product = Product::where( 'pid', $request->pid )->first();
    if( $request->id ) {
      $product = Product::where( 'id', $request->id )->first();
    }
    if( $request->vendor ) {
      $vendor = $request->vendor;
      if( $product !== null ) {
        $this->cartService->setForVendor();
        $this->cartService->setCustomerId( $vendor );
        $this->cartService->add( $product->id, 1, $product->purchase_price );
        return response()->json( [ 'status' => 'ok', 'message' => 'Stock Added to Cart' ], 200 );
      } else {
        return response()->json( [ 'status' => 'error', 'message' => 'Product Not Found!' ], 400 );
      }
    } elseif( $request->order ) {
      $order = $request->order;
      if( $product !== null ) {
        $this->cartService->setForOrder();
        $this->cartService->setCustomerId( $order );
        $this->cartService->add( $product->id, 1, $product->purchase_price,
          [ 'purchase_price' => $product->purchase_price, 'taxable_price' => $product->taxable_price ] );
        return response()->json( [ 'status' => 'ok', 'message' => 'Stock Added to Cart' ], 200 );
      } else {
        return response()->json( [ 'status' => 'error', 'message' => 'Product Not Found!' ], 400 );
      }
    } else {
      $customer = 1;
      if( $request->customer ) {
        $customer = $request->customer;
      }
      if( $product !== null ) {
        $this->cartService->setCustomerId( $customer );
        $this->cartService->add( $product->id, 1, $product->sale_price,
          [ 'purchase_price' => $product->purchase_price, 'taxable_price' => $product->taxable_price ] );
        return response()->json( [ 'status' => 'ok', 'message' => 'Stock Added to Cart' ], 200 );
      } else {
        return response()->json( [ 'status' => 'error', 'message' => 'Product Not Found!' ], 400 );
      }
    }

  }

  public
  function addToCart(
    Request  $request,
    Customer $customer
  ) {
    $this->cartService->setCustomerId( $customer->id );

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

  public function deleteFromCart( Request $request, Customer $customer )
  {

    $this->cartService->setCustomerId( $customer->id );
    $this->cartService->remove( $request->id );
    return response()->json( [ 'status' => 'ok', 'message' => 'Product Deleted from Cart' ], 200 );

  }

  public
  function emptyCart(
    Customer $customer
  ) {
    $this->cartService->setCustomerId( $customer->id );
    $this->cartService->trash();
    return response()->json( [ 'status' => 'ok', 'message' => 'Cart Deleted Successfully' ], 200 );

  }

  public function store( Request $request, Customer $customer )
  {
    $paid = $request->amount;
    $total = $request->total;
    $type = $request->payment_type;
    $bank = 0;
    if( $request->payment_id ) {
      $type = name( 'Bank', $request->payment_id );
      $bank = $request->payment_id;
    }
    $data = [
      'customer_id'      => $customer->id,
      'payment_type'     => $type,
      'date'             => $request->date,
      'discount_type'    => $request->discount_type,
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

    $order = Order::create( $data );

    foreach( $request->products as $product ) {

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

    Account::create( [
      'party_type' => Customer::class,
      'party_id'   => $customer->id,
      'debit'      => $total,
      'bank_id'    => 0,
      'comments'   => 'Order No.' . $order->id,
    ] );

    if( $paid > 0 ) {
      Account::create( [
        'party_type' => Customer::class,
        'party_id'   => $customer->id,
        'credit'     => $paid,
        'bank_id'    => $bank,
        'comments'   => 'Order No.' . $order->id,
      ] );
    }

    /* } else {
         $installment = $request->installment;
         for( $i = 1; $i <= $installment; $i++ ) {
             $sche_date = $request->scheduled_on;
             if( $i > 1 ) {
                 $date = new Carbon( $sche_date );
                 $sche_date = $date->addDays( 7 * $i );
             }
             Payment::create( [
                 'order_type'        => Order::class,
                 'order_id'          => $order->id,
                 'amount'            => $paid,
                 'payment_method_id' => $request->payment_method_id,
                 'scheduled_on'      => $sche_date,
                 'completed_on'      => $sche_date,
                 'status'            => 'Scheduled',
             ] );
         }

     }*/

    if( $request->printer ) {
      Option::set( 'printer', $request->printer );
    }
    $this->cartService->setCustomerId( $customer->id );
    $this->cartService->trash();

    return response()->json( [ 'status' => 'ok', 'message' => 'Order Added Successfully', 'url' => url( 'admin/order/invoice/' . $order->id ) ] );

  }

  /**
   * @param Order $order
   * @return array|string[]
   */
  public function destroy( Order $order ) : array
  {
    foreach( $order->items as $item ) {

      for( $i = 1; $i <= $item->stock; $i++ ) {
        $prod = Inventory::where( 'product_id', $item->product_id )
                         ->where( 'order_id', $order->id )
                         ->where( 'status', 'Sold' )->first();

        $prod->update( [
          'status'   => 'Available',
          'order_id' => 0
        ] );

      }

      InventoryLog::where( 'order_id', $order->id )
                  ->where( 'order_type', Order::class )->delete();

      $item->delete();
    }

    Account::create( [
      'party_type' => Customer::class,
      'party_id'   => $order->customer->id,
      'credit'     => $order->total,
      'comments'   => 'Order No.' . $order->id . ' Deleted',
    ] );

    $order->delete();
    return [ 'status' => 'ok', 'message' => 'Order Deleted' ];
  }
}
