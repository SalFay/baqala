<?php

use App\Models\Account;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Deletes the old invoices #12000 - #12999 that show up as 9 Nov 2026 sales on the live
 * site (their sale date is 2026-11-09, but they are old March 2026 invoices).
 *
 * Each one is deleted exactly like the Delete button on an order does
 * (OrderController::destroy): sold stock goes back to Available, the order lines and
 * inventory log rows are deleted, the sale and its payment leave the customer ledger,
 * and the order is deleted. All are soft deletes (deleted_at), so nothing is wiped.
 *
 * Only invoices #12000 - #12999 dated 2026-11-09 are touched; real 9 Nov sales
 * (#21825 on) never are. Running it again finds nothing more to delete.
 */
class DeleteOldInvoicesDated9Nov extends Migration
{
  private const FIRST = 12000;
  private const LAST = 12999;
  private const DATE = '2026-11-09';

  public function up()
  {
    $orders = Order::query()->without( 'customer' )
                   ->where( 'date', self::DATE )
                   ->whereBetween( 'id', [ self::FIRST, self::LAST ] )
                   ->orderBy( 'id' )
                   ->get();

    DB::transaction( function() use ( $orders ) {
      foreach( $orders as $order ) {
        foreach( $order->items as $item ) {
          Inventory::release( $order->id, $item->product_id, (int) $item->stock );
          $item->delete();
        }
        InventoryLog::where( 'order_id', $order->id )->where( 'order_type', Order::class )->delete();
        Account::forOrder( $order->id )->delete();
        $order->delete();
      }
    } );

    if( app()->runningInConsole() ) {
      echo '  Deleted ' . $orders->count() . ' old invoice(s) dated 9 Nov 2026'
        . ( $orders->isEmpty() ? '' : ': #' . $orders->pluck( 'id' )->implode( ', #' ) ) . "\n";
    }
  }

  public function down()
  {
    // Not undone automatically. The rows are only soft-deleted (deleted_at is set),
    // so they can still be restored by hand if ever needed.
  }
}
