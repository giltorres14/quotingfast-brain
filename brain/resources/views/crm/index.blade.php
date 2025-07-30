@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6">CRM Dashboard</h1>
    <div class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Clients</h2>
        <table class="min-w-full bg-white shadow rounded mb-4">
            <thead>
                <tr>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Email</th>
                    <th class="px-4 py-2">Phone</th>
                    <th class="px-4 py-2">Company</th>
                </tr>
            </thead>
            <tbody>
            @foreach($clients as $client)
                <tr>
                    <td class="border px-4 py-2">{{ $client->name }}</td>
                    <td class="border px-4 py-2">{{ $client->email }}</td>
                    <td class="border px-4 py-2">{{ $client->phone }}</td>
                    <td class="border px-4 py-2">{{ $client->company }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div>
        <h2 class="text-2xl font-semibold mb-4">Invoices</h2>
        <table class="min-w-full bg-white shadow rounded mb-4">
            <thead>
                <tr>
                    <th class="px-4 py-2">Client</th>
                    <th class="px-4 py-2">Amount</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Due Date</th>
                </tr>
            </thead>
            <tbody>
            @foreach($invoices as $invoice)
                <tr>
                    <td class="border px-4 py-2">{{ $invoice->client->name }}</td>
                    <td class="border px-4 py-2">{{ $invoice->amount }}</td>
                    <td class="border px-4 py-2">{{ ucfirst($invoice->status) }}</td>
                    <td class="border px-4 py-2">{{ $invoice->due_date->format('Y-m-d') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection 