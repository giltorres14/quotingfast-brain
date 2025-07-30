@extends('layouts.app')

@section('content')
<div class="p-4 bg-gray-100 space-y-4">

  <!-- HEADER -->
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold">All Leads</h1>
    <div class="text-gray-600">Total: {{ $total }}</div>
  </div>

  <!-- FILTER FORM -->
<form method="GET" class="space-y-4 mb-6">
  <!-- Row 1: Search, State, Source, Type, Button -->
  <div class="grid grid-cols-5 gap-4 items-end">
    <!-- 1) Search -->
    <div>
      <label class="block text-sm font-medium">Search</label>
      <input type="text" name="search" placeholder="Name, phone or email"
             value="{{ old('search', request('search')) }}"
             class="h-10 w-full border rounded px-3" />
    </div>
    <!-- 2) State (single-row, opens dropdown when clicked) -->
    <div x-data class="relative">
      <label class="block text-sm font-medium">State</label>
      <select name="state"
              class="h-10 w-full border rounded px-3 appearance-none">
        <option value="">All States</option>
        @foreach($states as $st)
          <option value="{{ $st }}" @selected($st==old('state', request('state')) )>
            {{ $st }}
          </option>
        @endforeach
      </select>
    </div>
    <!-- 3) Source -->
    <div>
      <label class="block text-sm font-medium">Source</label>
      <select name="source"
              class="h-10 w-full border rounded px-3">
      <option value="">All Sources</option>
      @foreach($sources as $src)
          <option value="{{ $src }}" @selected($src==old('source', request('source')) )>
            {{ $src }}
          </option>
        @endforeach
      </select>
    </div>
    <!-- 4) Type (single-row, opens dropdown when clicked) -->
    <div>
      <label class="block text-sm font-medium">Type</label>
      <select name="type[]" multiple
              class="h-10 w-full border rounded px-3"
              size="1">
        @foreach($types as $t)
          <option value="{{ $t }}"
            @selected(in_array($t, old('type', request('type', []))))>
            {{ $t }}
          </option>
        @endforeach
      </select>
    </div>
    <!-- 5) Filter button -->
    <div>
      <button type="submit"
              class="h-10 w-full bg-blue-600 text-white rounded">
        Filter
      </button>
    </div>
    </div>

  <!-- Row 2: Date filters -->
  <div class="grid grid-cols-4 gap-4 items-end">
    <div>
      <label class="block text-sm font-medium">Join Date From</label>
      <input type="date" name="join_date_from"
             value="{{ old('join_date_from',$join_date_from) }}"
             class="h-10 w-full border rounded px-3" />
    </div>
    <div>
      <label class="block text-sm font-medium">Join Date To</label>
      <input type="date" name="join_date_to"
             value="{{ old('join_date_to',$join_date_to) }}"
             class="h-10 w-full border rounded px-3" />
    </div>
    <div>
      <label class="block text-sm font-medium">Received From</label>
      <input type="date" name="received_date_from"
             value="{{ old('received_date_from',$received_date_from) }}"
             class="h-10 w-full border rounded px-3" />
    </div>
          <div>
      <label class="block text-sm font-medium">Received To</label>
      <input type="date" name="received_date_to"
             value="{{ old('received_date_to',$received_date_to) }}"
             class="h-10 w-full border rounded px-3" />
            </div>
          </div>
</form>

  <!-- LEADS TABLE -->
  <div class="overflow-x-auto bg-white shadow rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name & Phone</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date Entered</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">City, State</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lead Type</th>
          <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        @foreach($leads as $lead)
          <tr>
            <!-- Name & Phone -->
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ $lead->first_name }} {{ $lead->last_name }}</div>
              <div class="text-sm text-gray-600">
                @php
                  $p = preg_replace('/\D+/', '', $lead->phone);
                  $formatted = strlen($p)==10
                    ? '('.substr($p,0,3).')'.substr($p,3,3).'-'.substr($p,6)
                    : $lead->phone;
                @endphp
                {{ $formatted }}
        </div>
            </td>

            <!-- Date Entered -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
              {{ optional($lead->received_at)->format('m-d-y') ?? now()->format('m-d-y') }}
            </td>

            <!-- City, State -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
              {{ $lead->city }}, {{ $lead->state }}
            </td>

            <!-- Seller bubble -->
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
            {{ $lead->source }}
          </span>
            </td>

            <!-- Lead Type bubble -->
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                {{ $lead->type==='internet' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
            {{ ucfirst($lead->type) }}
          </span>
            </td>

            <!-- Actions -->
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
              <a href="{{ route('leads.show', $lead) }}" class="text-white bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded">
                View
              </a>
              <a href="{{ route('leads.edit', $lead) }}" class="text-gray-700 bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded">
                Edit
              </a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
        </div>

  <!-- PAGINATION -->
    <div class="mt-4">
      {{ $leads->withQueryString()->links() }}
    </div>
</div>
@endsection 