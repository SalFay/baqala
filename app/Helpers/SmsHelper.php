<?php

namespace App\Helpers;

use App\Mail\EmailMessage;
use App\Services\SendSmsService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Mail;

class SmsHelper
{
  private static $api;
  
  private static function initApi()
  {
    if( !self::$api instanceof SendSmsService ) {
      self::$api = new SendSmsService();
    }
    return self::$api;
  }// initApi
  
  /**
   * @throws RequestException
   */
  public static function sendSMS( $to, $message )
  {
    return self::initApi()->send( $to, $message );
  }// sendSMS
  
  public static function sendToAll( $to, $message )
  {
    return self::initApi()->sendToAll( $to, $message );
  }// sendSMS
  
  public static function checkListofSMS()
  {
    return self::initApi()->checkRemainingSMS();
  }// sendSMS
  
  public static function deliveryStatus()
  {
    return self::initApi()->deliveryStatus();
  }// sendSMS
  
  public static function sendEmail( $data, $user )
  {
    Mail::to( $user )->send( new EmailMessage( $user, $data ) );
  }// sendSMS
  
}// SmsHelper
