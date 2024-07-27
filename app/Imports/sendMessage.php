<?php

namespace App\Imports;

use App\Helpers\SmsHelper;
use App\Mail\SendEmail;
use App\Notifications\EmailNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Concerns\ToModel;
use function PHPUnit\Framework\isNull;

class sendMessage implements ToModel
{
  
  private $message;
  private $type;
  
  public function __construct( $message, $type )
  {
    $this->message = $message;
    $this->type = $type;
  }
  
  /**
   * @param array $row
   * @return Model|Model[]|null|void
   * @throws \Illuminate\Http\Client\RequestException
   */
  public function model( $row )
  {
    
    if( $this->type === 'SMS' ) {
      $to = (int) implode( ',', $row );
      if( !isNull( $to ) || $to !== "" && $to !== 0 ) {
        SmsHelper::sendSMS( $to, $this->message );
      }
      
    }
    
    if( $this->type === 'EMAIL' ) {
      
      $email = implode( ',', $row );
      if( !isNull( $email ) || $email !== "" ) {
        $body = [
          'greeting' => 'Hi,',
          'body'     => $this->message
        ];
        Notification::route( 'mail', $email )
                    ->notify( new EmailNotification( $body ) );
      }
    }
  }
  
}
