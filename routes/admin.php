<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get( '/', [ DashboardController::class, 'index' ] )->name( 'dashboard' );
Route::get( '/profile', [ ProfileController::class, 'edit' ] )->name( 'profile.edit' );
Route::patch( '/profile', [ ProfileController::class, 'update' ] )->name( 'profile.update' );
Route::delete( '/profile', [ ProfileController::class, 'destroy' ] )->name( 'profile.destroy' );

