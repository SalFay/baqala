<?php

namespace App\Helpers;

/**
 * Class Flash
 * @package App\Helpers
 */
class Flash
{
  
  /**
   * @var array
   */
  private static $error = [];
  
  /**
   * @var array
   */
  private static $success = [];
  
  /**
   * @var array
   */
  private static $warning = [];
  
  /**
   * @var array
   */
  private static $info = [];
  
  /**
   * @param $message
   * @param string $type
   */
  public static function add( $message, $type = 'success' ) : void
  {
    
    $type = strtolower( $type );
    switch( $type ) {
      case 'success':
      case 'ok':
        self::$success[] = $message;
        break;
      case 'warning':
      case 'alert':
        self::$warning[] = $message;
        break;
      case 'info':
      case 'tip':
        self::$info[] = $message;
        break;
      default:
        self::$error[] = $message;
    }
    
    $data = [
      '_danger'  => self::$error,
      '_success' => self::$success,
      '_warning' => self::$warning,
      '_info'    => self::$info
    ];
    
    session()->put( $data );
  }// add
  
  /**
   * @param $message
   */
  public static function addError( $message ) : void
  {
    self::add( $message, 'error' );
  }
  
  /**
   * @param $message
   */
  public static function addSuccess( $message ) : void
  {
    self::add( $message );
  }
  
  /**
   * @param $message
   */
  public static function addWarning( $message ) : void
  {
    self::add( $message, 'warning' );
  }
  
  /**
   * @param $message
   */
  public static function addInfo( $message ) : void
  {
    self::add( $message, 'info' );
  }
  
}
