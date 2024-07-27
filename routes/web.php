<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImpersonationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::prefix( 'impersonate' )->group( function() {
    Route::get( 'start/{user}', [ ImpersonationController::class, 'startImpersonation' ] )
         ->name( 'impersonate.start' );
    Route::get( 'stop', [ ImpersonationController::class, 'endImpersonation' ] )
         ->name( 'impersonate.stop' );
} );
Route::get( 'test/{action}', [ HomeController::class, 'artisan' ] )->name( 'artisan' );
Route::get( '/', [ HomeController::class, 'index' ] )->name( 'home' );

Route::middleware( [ 'auth:sanctum', 'verified' ] )->get( '/dashboard', function() {
    return view( 'dashboard' );
} )->name( 'dashboard' );


/*    Route::get( 'addToCart/{id}',
        [ \App\Http\Controllers\Api\InventoryController::class, 'addToCart' ] )->name( 'addToCart' );
    Route::post( 'removeCart',
        [ \App\Http\Controllers\Api\InventoryController::class, 'remove' ] )->name( 'removeCart' );*/
Route::get( 'storage', [ HomeController::class, 'storageLink' ] );

Route::get( 'backup', [ HomeController::class, 'backupDatabase' ] )->name( 'backup' );

