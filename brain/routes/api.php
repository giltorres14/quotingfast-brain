<?php

use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// LeadsQuotingFast webhook endpoint
Route::post('/webhook/leadsquotingfast', function (Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'LeadsQuotingFast webhook received successfully',
        'timestamp' => now()->toISOString(),
        'data_received' => $request->all()
    ], 200);
});

// Test endpoint to verify API is working
Route::get('/webhook/leadsquotingfast', function (Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'LeadsQuotingFast webhook endpoint is active',
        'methods_supported' => ['POST'],
        'timestamp' => now()->toISOString()
    ], 200);
});