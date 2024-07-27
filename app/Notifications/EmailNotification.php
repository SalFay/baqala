<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EmailNotification extends Notification implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
  
  public $message;
  
  /**
   * @param $message
   */
  public function __construct( $message )
  {
    $this->message = $message;
  }
  
  /**
   * Get the notification's delivery channels.
   * @param mixed $notifiable
   * @return array
   */
  public function via( $notifiable )
  {
    return [ 'mail', 'database' ];
  }
  
  /**
   * @param $notifiable
   * @return MailMessage
   */
  public function toMail( $notifiable )
  {
    
    return ( new MailMessage )
      ->greeting( $this->message[ 'greeting' ] )
      ->line( $this->message[ 'body' ] );
  }
  
  /**
   * Get the array representation of the notification.
   * @param mixed $notifiable
   * @return array
   */
  public function toArray( $notifiable )
  {
    return [
      //
    ];
  }
}
