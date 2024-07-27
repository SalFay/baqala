<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class EmailMessage extends Mailable
{
  use Queueable, SerializesModels;
  
  private $user;
  private $message;
  
  /**
   * Create a new message instance.
   * @return void
   */
  public function __construct( $email, $message )
  {
    $this->user = $email;
    $this->message = $message;
  }
  
  /**
   * Build the message.
   * @return $this
   */
  public function build()
  {
    activity()
      ->useLog( 'send-email-manual' )
      ->withProperty( 'email', $this->user )
      ->causedBy( \auth()->user() )
      ->log( $this->message[ 'message' ] );
    return $this->subject( env( 'app_name' ) )
                ->replyTo( $this->user, '' )
                ->view( 'emails.message', [ 'data' => $this->message ] );
  }
}
