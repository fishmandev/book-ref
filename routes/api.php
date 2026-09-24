<?php

use App\Http\Controllers\ReferralController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API
|--------------------------------------------------------------------------
|
| The current master is passed in the X-Master-Id header and added to the
| request attributes by the ResolveCurrentMaster middleware:
|
|     $master = $request->attributes->get('current_master');
|
| The three referral routes are defined in this file.
|
*/

Route::get('/ping', fn () => ['ok' => true]);

Route::post('/referrals/attach', [ReferralController::class, 'attach']);

// TODO: GET /api/referrals/my
// TODO: GET /api/referrals/earnings
