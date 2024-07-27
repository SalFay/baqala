<?php

namespace App\Http\Controllers;

use App\DataTables\EmailDataTable;
use App\DataTables\SMSDataTable;
use App\Helpers\SmsHelper;
use App\Imports\sendMessage;
use App\Models\Post;
use App\Notifications\EmailNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class SMSController extends Controller
{
  public function index( SMSDataTable $dataTable )
  {
    $smsResponse = SmsHelper::checkListofSMS();
    //	$delivery = SmsHelper::deliveryStatus();
    return $dataTable->render( 'admin.sms.index', [ "smsHistory" => $smsResponse ] );
  }
  
  public function emails( EmailDataTable $dataTable )
  {
    return $dataTable->render( 'admin.sms.emails' );
  }
  
  public function file()
  {
    return view( 'admin.sms.file' );
  }
  
  public function destroy( Request $request )
  {
    Auth::guard( 'web' )->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route( 'dashboard' );
  }
  
  public function sendMessage( Request $request )
  {
    
    $request->validate( [
      "type"    => "required",
      "message" => "required",
    ] );
    $message = $request[ 'message' ];
    $type = $request[ 'type' ];
    if( !$request->file && empty( $request->numbers ) ) {
      
      return new JsonResponse( [ 'status' => 'error', 'message' => 'Please Select File OR Enter Numbers / Emails' ],
        400 );
      
    }
    if( $request->file ) {
      $request->validate( [
        "file" => "required|mimes:xlsx, csv, xls",
      ] );
      $path = $request->file( 'file' );
      try {
        \Excel::import( new sendMessage( $message, $type ), $path );
        if( $type === 'SMS' ) {
          return new JsonResponse( [ 'status' => 'ok', 'message' => 'SMS sent successfully' ], 200 );
        }
        if( $type === 'EMAIL' ) {
          return new JsonResponse( [ 'status' => 'ok', 'message' => 'Email sent successfully' ], 200 );
        }
      } catch ( Exception $e ) {
        return new JsonResponse( [ 'status' => 'error', 'message' => $e->getMessage() ], 400 );
        
      }
    }
    if( $request->numbers ) {
      if( $type === 'SMS' ) {
        $pos = strpos( $request->numbers, '@' );
        if( $pos === false ) {
          try {
            $numbers = explode( ',', $request->numbers );
            SmsHelper::sendToAll( $request->numbers, $message );
            foreach( $numbers as $num ) {
              activity()
                ->useLog( 'send-sms-manual' )
                ->withProperty( 'sms', $num )
                ->causedBy( \auth()->user() )
                ->log( $message );
            }
            return new JsonResponse( [ 'status' => 'ok', 'message' => 'SMS sent successfully' ], 200 );
          } catch ( Exception $exception ) {
            $errors = $exception->getMessage();
            return new JsonResponse( [ 'status' => 'error', 'message' => $errors ] );
          }
        }
        return new JsonResponse( [ 'status' => 'error', 'message' => 'Please Enter Numbers for SMS' ] );
        
      }
      
      if( $type === 'EMAIL' ) {
        $pos = strpos( $request->numbers, '@' );
        if( $pos !== false ) {
          $message = $request[ 'message' ];
          $emails = explode( ',', $request->numbers );
          foreach( $emails as $email ) {
            try {
              $body = [
                'greeting' => 'Hi,',
                'body'     => $message
              ];
              Notification::route( 'mail', $email )
                          ->notify( new EmailNotification( $body ) );
              //	SmsHelper::sendEmail( $message, $email );
              activity()
                ->useLog( 'send-email-manual' )
                ->withProperty( 'email', $email )
                ->causedBy( \auth()->user() )
                ->log( $message );
            } catch ( Exception $exception ) {
              $errors = $exception->getMessage();
              activity()
                ->useLog( 'not-send-email-manual' )
                ->withProperty( 'email', $email )
                ->causedBy( \auth()->user() )
                ->log( $message );
              return new JsonResponse( [ 'status' => 'error', 'message' => $errors ] );
            }
          }
          return new JsonResponse( [ 'status' => 'ok', 'message' => 'Email sent successfully' ], 200 );
        }
        return new JsonResponse( [ 'status' => 'error', 'message' => 'Please Enter Emails' ] );
        
      }
      
    }
    
  }
  
  public function sendMessageToUsers( Request $request )
  {
    $request->validate( [
      "users"   => "required|array",
      "message" => "required",
    ] );
    $message = $request[ 'message' ];
    $post = Post::find( $request[ 'post_id' ] ) ?? \auth()->user();
    $users = array_filter( $request[ 'users' ] );
    $sms = $request[ 'sms' ];
    $email = $request[ 'email' ];
    if( $sms === null && $email === null ) {
      return new JsonResponse( [ 'status' => 'error', 'message' => 'Please select from SMS or Email' ] );
      
    }
    if( !empty( $users ) ) {
      $tmpPhoneList = [];
      $totalUser = count( $users );
      foreach( $users as $user ) {
        $user = \App\Models\User::find( $user );
        if( !empty( $user->email ) && $email !== null ) {
          try {
            $body = [
              'greeting' => 'Hi,',
              'body'     => $message
            ];
            Notification::route( 'mail', $user->email )
                        ->notify( new EmailNotification( $body ) );
            //SmsHelper::sendEmail( $data, $user->email );
            activity()
              ->useLog( 'send-email' )
              ->withProperty( 'email', $user->email )
              ->performedOn( $post )
              ->causedBy( \auth()->user() )
              ->log( $message );
          } catch ( Exception $exception ) {
            $errors = $exception->getMessage();
            activity()
              ->useLog( 'not-send-email' )
              ->withProperty( 'email', $user->email )
              ->performedOn( $post )
              ->causedBy( \auth()->user() )
              ->log( $message );
            return new JsonResponse( [ 'status' => 'error', 'message' => $errors ] );
          }
          
        }
        if( empty( $user->phone_no ) ) {
          continue;
        }
        
        $to = $user->phone_no;
        try {
          $to = ltrim( $to, '0' );
          $to = '92' . $to;
        } catch ( Exception $e ) {
          $errors = $e->getMessage();
          return new JsonResponse( [ 'status' => 'error', 'message' => $errors ] );
        }
        $phonesList = str_replace( '-', '', $to );
        array_push( $tmpPhoneList, $phonesList );
      }
      if( $sms !== null ) {
        $phonesList = $tmpPhoneList;
        $phonesStr = implode( ",", $phonesList );
        try {
          activity()
            ->useLog( 'send-sms' )
            ->withProperty( 'sms', $to )
            ->performedOn( $post )
            ->causedBy( \auth()->user() )
            ->log( $message );
          SmsHelper::sendToAll( $phonesStr, $message );
        } catch ( Exception $exception ) {
          $errors = $exception->getMessage();
          return new JsonResponse( [ 'status' => 'error', 'message' => $errors ] );
        }
      }
      
      $msg = 'Message sent to ' . $totalUser . ' users successfully.';
      return new JsonResponse( [ 'status' => 'ok', 'message' => $msg ] );
    } else {
      return new JsonResponse( [ 'status' => 'error', 'message' => 'Please Select User' ] );
    }
    
  }
  
}
