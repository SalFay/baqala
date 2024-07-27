<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
  
  public function addStock( Request $request )
  {
    dd( $request->all() );
    
  }
  
  public function addSale( Request $request )
  {
    dd( $request->all() );
    
  }
  
  /*public function addToCart( $id )
  {
      $product = Product::find( $id );
      if( !$product ) {
          abort( 404 );
      }
      $cart = session()->get( 'cart' );
      // if cart is empty then this the first product
      if( !$cart ) {
          $cart = [
              $id => [
                  "name"     => $product->name,
                  "quantity" => 1,
                  "pprice"   => $product->purchase_price,
                  "sprice"   => $product->sale_price
              ]
          ];
          session()->put( 'cart', $cart );
          return response()->json( [ 'status' => 'ok', 'message' => 'Product added to cart successfully!' ],
              200 );
      }

      // if cart not empty then check if this product exist then increment quantity
      if( isset( $cart[ $id ] ) ) {
          $cart[ $id ][ 'quantity' ]++;
          session()->put( 'cart', $cart );
          return response()->json( [ 'status' => 'ok', 'message' => 'Product added to cart successfully!' ],
              200 );
      }

      // if item not exist in cart then add to cart with quantity = 1
      $cart[ $id ] = [
          "name"     => $product->name,
          "quantity" => 1,
          "pprice"   => $product->purchase_price,
          "sprice"   => $product->sale_price
      ];
      session()->put( 'cart', $cart );
      return response()->json( [ 'status' => 'ok', 'message' => 'Product added to cart successfully!' ], 200 );
  }*/
  
}
