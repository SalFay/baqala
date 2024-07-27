<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use http\Env\Request;
use Yajra\DataTables\DataTables;

class InventoryController extends Controller
{
  
  public function products()
  {
    $table = DataTables::of( Product::query() );
    
    $table->addColumn( 'name', static function( Product $row ) {
      return '<b>' . $row->full_name . '<br>' . $row->arabic_name . '</b>

                <div class="input-group">
                 <div class="input-group-append">
                    <a href="" data-id=' . $row->id . '
                    class="btn btn-danger"
                    data-action="minus">
                    <i class="fa fa-minus"></i></a>
                  </div>
                <input type="number"
                style="max-width: 100px !important;"
                class="form-control text-center colorful stock' . "{$row->id}" . '"
                type="number" value="1" min="1" />
                <div class="input-group-append">
                    <a href="" data-id=' . $row->id . '
                    class="btn btn-success"
                    data-action="plus">
                    <i class="icon-add"></i></a>
                  </div>
                  <div class="input-group-append">
                   <a href="#"
                   data-id=' . $row->id . '
                   data-name="' . $row->full_name . '"
                   data-pprice=' . $row->purchase_price . '
                    class="btn btn-secondary"
                    data-action="addCart">
                    <i class="icon-cart-add"></i></a>
                </div>
</div>';
    } );
    $table->addColumn( 'purchase_price', static function( Product $row ) {
      return $row->purchase_price;
    } );
    
    $table->rawColumns( [ 'name', 'purchase_price' ] );
    return $table->toJson();
  }
  
  public function customerProducts()
  {
    $table = DataTables::of( Product::query() );
    
    $table->addColumn( 'name', static function( Product $row ) {
      return '<b>' . $row->full_name . '<br>' . $row->arabic_name . '</b>
                <div class="input-group">
                 <div class="input-group-append">
                    <a href="" data-id=' . $row->id . '
                    class="btn btn-sm btn-danger"
                    data-action="minus">
                    <i class="fa fa-minus"></i></a>
                  </div>
                <input type="number"
                style="max-width: 100px !important;"
                class="form-control text-center colorful stock' . "{$row->id}" . '"
                type="number" value="1" min="1" />
                <div class="input-group-append">
                    <a href="" data-id=' . $row->id . '
                    class="btn btn-sm btn-success"
                    data-action="plus">
                    <i class="icon-add"></i></a>
                  </div>
                  <div class="input-group-append">
                   <a href="#"
                   data-id=' . $row->id . '
                   data-name="' . $row->full_name . '"
                   data-price=' . $row->sale_price . '
                   data-actual=' . $row->taxable_price . '
                   data-purchase_price=' . $row->purchase_price . '
                    class="btn btn-sm  btn-secondary"
                    data-action="addCart">
                    <i class="icon-cart-add"></i></a>
                </div>
</div>';
    } );
    $table->editColumn( 'sale_price', static function( Product $row ) {
      return $row->taxable_price ? $row->taxable_price : $row->sale_price;
    } );
    
    $table->rawColumns( [ 'name' ] );
    return $table->toJson();
  }
  
}
