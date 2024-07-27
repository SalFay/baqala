<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class LogController extends Controller
{
  public function index()
  {
    $activities = Activity::orderBy( 'id', 'DESC' )->paginate( 5 );
    return view( 'admin.logs.index', [ "auth_logs" => $activities ] );
  }
  
  public function filter( Request $request )
  {
    
    if( !empty( $request->all() ) ) {
      $role = $request->role;
      $user = $request->user;
      $type = $request->type;
      $query = Activity::query();
      
      if( $role ) {
        $users = User::query();
        $users = $users->where( 'role_id', $role )->pluck( 'id' );
        $query->whereIn( 'causer_id', $users )->orWhereIn( 'subject_id', $users );
      }
      if( $user ) {
        $query->where( 'causer_id', $user )->orWhere( 'subject_id', $user );
      }
      
      if( !empty( $type ) ) {
        $log = '';
        switch( $type ) {
          case 'reg':
            $log = [ 'registered-sms', 'registered-email' ];
            break;
          case 'manual':
            $log = [ 'send-sms-manual', 'not-send-sms-manual', 'send-email-manual', 'not-send-email-manual' ];
            break;
          case 'project':
            $log = [ 'send-sms', 'send-email' ];
            break;
        }
        
        if( $type === 'query' ) {
          $query->where( 'log_name', 'LIKE', '%' . $type . '%' );
        } elseif( $type === 'job' ) {
          $query->where( 'log_name', 'LIKE', '%' . $type . '%' );
        } else {
          $query->whereIn( 'log_name', $log );
        }
        
      }
      
      $activities = $query->orderBy( 'id', 'DESC' )->get();
      $view = view( 'admin.logs.timeline', [ "auth_logs" => $activities ] )->render();
      return response()->json( [ 'status' => 'ok', 'view' => $view ], 200 );
    }
    
    return response()->json( [ 'status' => 'error', 'message' => 'Please Select from Filters' ], 400 );
    
  }
}
