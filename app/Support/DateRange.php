<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * A from/to day range read from "Y-m-d to Y-m-d" (or a single "Y-m-d"), as the
 * flatpickr range pickers send it. Shared by the list and report pages so they
 * all read dates and offer the same presets as the dashboard.
 */
class DateRange
{
  public Carbon $from;
  public Carbon $to;

  public function __construct( Carbon $from, Carbon $to )
  {
    [ $this->from, $this->to ] = $from->lte( $to ) ? [ $from, $to ] : [ $to, $from ];
  }

  /**
   * @param string|null $input "Y-m-d to Y-m-d" or "Y-m-d"
   * @param string $default today | month | year (used when the input is empty or unreadable)
   */
  public static function parse( ?string $input, string $default = 'today' ) : self
  {
    if( $input ) {
      try {
        $parts = array_map( 'trim', explode( 'to', $input ) );
        return new self( Carbon::parse( $parts[ 0 ] )->startOfDay(), Carbon::parse( $parts[ 1 ] ?? $parts[ 0 ] )->startOfDay() );
      } catch( \Throwable $e ) {
        // fall through to the default
      }
    }
    $today = now()->startOfDay();
    switch( $default ) {
      case 'month':
        return new self( $today->copy()->startOfMonth(), $today );
      case 'year':
        return new self( $today->copy()->startOfYear(), $today );
      default:
        return new self( $today, $today->copy() );
    }
  }

  /** "Y-m-d to Y-m-d", the picker's own format. */
  public function value() : string
  {
    return $this->from->toDateString() . ' to ' . $this->to->toDateString();
  }

  /** [from, to] as Y-m-d, for whereBetween on a DATE column. */
  public function dates() : array
  {
    return [ $this->from->toDateString(), $this->to->toDateString() ];
  }

  /** [from 00:00:00, to 23:59:59], for whereBetween on a TIMESTAMP column. */
  public function moments() : array
  {
    return [ $this->from->format( 'Y-m-d 00:00:00' ), $this->to->format( 'Y-m-d 23:59:59' ) ];
  }

  /** "09-Nov-2026" or "01-Nov-2026 – 09-Nov-2026". */
  public function label() : string
  {
    $f = fn( Carbon $d ) => $d->format( 'd-M-Y' );
    return $this->from->equalTo( $this->to ) ? $f( $this->from ) : $f( $this->from ) . ' – ' . $f( $this->to );
  }

  /** Label => range value, the same choices as the dashboard. */
  public static function presets() : array
  {
    $d = fn( Carbon $a, Carbon $b ) => $a->toDateString() . ' to ' . $b->toDateString();
    $today = now()->startOfDay();
    return [
      'Today'       => $d( $today, $today ),
      'Yesterday'   => $d( $today->copy()->subDay(), $today->copy()->subDay() ),
      'Last 7 days' => $d( $today->copy()->subDays( 6 ), $today ),
      'This month'  => $d( $today->copy()->startOfMonth(), $today ),
      'Last month'  => $d( $today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth() ),
      'This year'   => $d( $today->copy()->startOfYear(), $today ),
    ];
  }
}
