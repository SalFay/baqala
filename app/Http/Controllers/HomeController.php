<?php

namespace App\Http\Controllers;

use App\Mail\TestEmail;
use App\Models\Site;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class HomeController extends Controller
{
  /**
   * Create a new controller instance.
   * @return void
   */
  public function __construct()
  {
    //$this->middleware( 'auth' );
  }
  
  /**
   * Show the application dashboard.
   * @return Renderable
   */
  public function index()
  {
    return redirect( 'login' );
  }
  
  public function storageLink()
  {
    Artisan::call( 'storage:link' );
  }
  
  public function backupDatabase()
  {
    Artisan::call( 'backup:run' );
    
    return Artisan::output();
    
  }
  
  /**
   * @param $action
   * @return JsonResponse
   */
  public function artisan( $action ) : JsonResponse
  {
    if( $action === 'migrate' ) {
      Artisan::call( 'migrate' );
    } elseif( $action === 'seed' ) {
      Artisan::call( 'db:seed' );
    } elseif( $action === 'email' ) {
      $output = Mail::to( [ 'haroonyousaf80@gmail.com' ] )->send( new TestEmail() );
      return JsonResponse::create( [ 'message' => $output ] );
    }
    
    return JsonResponse::create( [ Artisan::output() ] );
  }// artisan
  
  /**
   * @param User $user
   * @return Application|RedirectResponse|Redirector
   */
  public function startImpersonation( User $user )
  {
    Auth::user()->impersonate( $user );
    return redirect( '/admin' );
  }// startImpersonation
  
  /**
   * @return Application|RedirectResponse|Redirector
   */
  public function leaveImpersonation()
  {
    Auth::user()->leaveImpersonation();
    return redirect( '/admin/user' );
  }
  
}
