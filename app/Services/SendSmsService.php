<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Http;

class SendSmsService
{
  private $client;
  
  public function __construct()
  {
    /**
     * Outreach
     */
    $this->client = Http::baseUrl( 'http://outreach.pk/api/sendsms.php/' );
    
    /**
     * Webnaxtor
     */
    // $this->client = Http::baseUrl( 'http://sms.webnaxtor.com/' );
    
  }
  
  /**
   * @param $to
   * @param $message
   * @return array|mixed|void
   * @throws RequestException
   */
  public function send( $to, $message )
  {
    $to = ltrim( $to, '0' );
    $to = '92' . $to;
    $url = 'sendsms/url';
    //$url = 'sendsms.php';
    $params = [
      /*   'apikey' => '96fbadd321c27cec711ec6b1ceea2a5f',
         'sender' => 'ETA',
         'phone'  => $to,//'92300xxxxxxx',
         'message' => $message,
       'operator' => 1*/
      
      'id'   => 'rchexpertxdev',
      'pass' => 'BrandedSMS2',
      'mask' => 'Xpertz Dev',
      'to'   => $to,//'92300xxxxxxx',
      'lang' => 'English',
      'msg'  => $message,
      'type' => 'json'
    ];
    $response = $this->client->acceptJson()->get( $url, $params );
    if( $response->successful() ) {
      return $response->json();
    }
    $response->throw();
  }
  
  public function sendToAll( $to, $message )
  {
    $url = 'sendsms/url';
    // $url = 'sendsms.php';
    $params = [
      /* 'apikey'  => '96fbadd321c27cec711ec6b1ceea2a5f',
       'sender'  => 'ETA',
       'phone'   => $to,
       'message' => $message,
       'operator' => 1*/
      
      'id'   => 'rchexpertxdev',
      'pass' => 'BrandedSMS2',
      'mask' => 'Xpertz Dev',
      'to'   => $to,//'92300xxxxxxx',
      'lang' => 'English',
      'msg'  => $message,
      'type' => 'json'
    ];
    $response = $this->client->acceptJson()->get( $url, $params );
    if( $response->successful() ) {
      return $response->json();
    }
    $response->throw();
  }
  
  public function checkRemainingSMS()
  {
    $url = 'balance/status';
    //$url = 'api/credit.php';
    
    /* $params = [
         'apikey' => '96fbadd321c27cec711ec6b1ceea2a5f'
     ];*/
    
    $params = [
      'id'   => 'rchexpertxdev',
      'pass' => 'BrandedSMS2'
    ];
    $response = $this->client->get( $url, $params );
    if( $response->successful() ) {
      return $response;
    }
    $response->throw();
  }
  
  public function deliveryStatus()
  {
    $url = 'delivery/status';
    $params = [
      'id'   => 'rchexpertxdev',
      'pass' => 'BrandedSMS2'
    ];
    $response = $this->client->get( $url, $params );
    if( $response->successful() ) {
      return $response;
    }
    $response->throw();
  }
}// SendSmsService
