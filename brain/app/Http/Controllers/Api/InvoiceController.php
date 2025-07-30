<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Http\Requests\StoreInvoiceRequest;
use App\Services\QuickBooksService;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Invoice::with('client', 'payments')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvoiceRequest $request, QuickBooksService $qb)
    {
        $invoice = Invoice::create($request->validated());
        try {
            $qb->createInvoice($invoice);
        } catch (\Exception $e) {
            Log::error('QuickBooks sync failed: ' . $e->getMessage());
        }
        return response()->json($invoice, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Invoice::with('client', 'payments')->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreInvoiceRequest $request, string $id, QuickBooksService $qb)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->update($request->validated());
        try {
            $qb->createInvoice($invoice);
        } catch (\Exception $e) {
            Log::error('QuickBooks sync failed: ' . $e->getMessage());
        }
        return response()->json($invoice);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();
        return response()->json(null, 204);
    }
}
