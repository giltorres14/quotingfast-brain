<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Http\Requests\StorePaymentRequest;
use App\Services\QuickBooksService;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Payment::with('invoice')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentRequest $request, QuickBooksService $qb)
    {
        $payment = Payment::create($request->validated());
        try {
            $qb->createPayment($payment);
        } catch (\Exception $e) {
            Log::error('QuickBooks payment sync failed: ' . $e->getMessage());
        }
        return response()->json($payment, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Payment::with('invoice')->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StorePaymentRequest $request, string $id)
    {
        $payment = Payment::findOrFail($id);
        $payment->update($request->validated());
        return response()->json($payment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();
        return response()->json(null, 204);
    }
}
