<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\Select2Controller;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\HomeController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */
Route::post( 'auth-with-channel', function() {
    /*if( !Auth::check() ) {
        abort( '403' );
    }*/
    $string = request( 'socket_id' ) . ':' . request( 'channel_name' );
    $signature = hash_hmac( 'sha256', $string, 'f5a3a4a413d82f0618e2' );
    $output = [
        'auth' => '29b747b2f10c33b68602' . ':' . $signature,
        'user' => Auth::user()
    ];
    return JsonResponse::create( $output );
} );

