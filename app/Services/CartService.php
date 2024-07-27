<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Session;

/**
 * Class CartService
 * @package App\Services
 */
class CartService
{
  private $for = 'client';
  
  /**
   * @var
   */
  private $id;
  
  private $key;
  
  /**
   *
   */
  public function setForClient()
  {
    $this->for = 'client';
  }
  
  /**
   *
   */
  public function setForVendor()
  {
    $this->for = 'vendor';
  }
  
  public function setForOrder()
  {
    $this->for = 'order';
  }
  
  /**
   * @param $id
   * @return $this
   */
  public function setCustomerId( $id )
  {
    $this->id = $id;
    return $this;
  }
  
  /**
   * @param       $id
   * @param       $qty
   * @param       $price
   * @param array $extra
   * @return $this
   */
  public function add( $id, $qty, $price, $extra = [] )
  {
    $cart = $this->getCart();
    
    if( isset( $cart[ $id ] ) ) {
      return $this->update( $id, $qty );
    }
    $cart[ $id ] = [
      'id'    => $id,
      'stock' => $qty,
      'price' => $price
    ];
    
    $cart[ $id ] = array_merge( $cart[ $id ], $extra );
    
    $this->saveCart( $cart );
    return $this;
  }
  
  public function update( $id, $qty )
  {
    $cart = $this->getCart();
    $cart[ $id ][ 'stock' ] += $qty;
    $this->saveCart( $cart );
    return $this;
    
  }
  
  public function updateWithPrice( $id, $qty, $price, $extra = [] )
  {
    
    $cart = $this->getCart();
    $cart[ $id ][ 'stock' ] = $qty;
    $cart[ $id ][ 'price' ] = $price;
    $cart[ $id ] = array_merge( $cart[ $id ], $extra );
    $this->saveCart( $cart );
    return $this;
  }
  
  public function remove( $id )
  {
    $cart = $this->getCart();
    unset( $cart[ $id ] );
    $this->saveCart( $cart );
    return $this;
  }
  
  public function trash()
  {
    $this->getCart();
    Session::remove( $this->key );
  }
  
  /**
   * @return float|int
   */
  public function getTotal()
  {
    $total = 0;
    $cart = $this->getCart();
    
    foreach( $cart as $product ) {
      $total += $product[ 'quantity' ] * $product[ 'price' ];
    }
    
    return $total;
  }
  
  /**
   * @return mixed
   */
  public function getCart()
  {
    if( $this->key === null ) {
      $this->key = '_cart_' . $this->for . '_' . $this->id;
    }
    
    return Session::get( $this->key, [] );
  }// initCart
  
  public function getContent()
  {
    $cart = $this->getCart();
    
    foreach( $cart as $id => $attribs ) {
      $product = Product::find( $id );
      $cart[ $id ][ 'product' ] = $product;
    }
    return $cart;
  }
  
  /**
   * @param $content
   * @return mixed
   */
  private function saveCart( $content )
  {
    Session::put( $this->key, $content );
    return $content;
  }
}
