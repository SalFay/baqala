<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
  public function startImpersonation( User $user )
  {
    Auth::user()->impersonate( $user );
    return redirect( \user()->redirection() );
  }
  
  public function endImpersonation()
  {
    Auth::user()->leaveImpersonation();
    return redirect()->route( 'admin.users' );
  }
}
