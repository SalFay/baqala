<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;

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
