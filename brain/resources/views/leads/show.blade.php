@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
  {{-- Lead Header Card --}}
  <div class="bg-white rounded-xl shadow-lg p-6 mb-8 flex items-center justify-between">
    <div class="flex items-center">
      {{-- Avatar circle: first letter of type, colored by type --}}
      <div
        class="h-16 w-16 rounded-full flex items-center justify-center text-white text-2xl font-bold"
        style="background-color: {{ $lead->type === 'internet' ? '#93c5fd' : '#ccc' }};"
      >
        {{ strtoupper(substr($lead->type, 0, 1)) }}
      </div>

      {{-- Name + metadata --}}
      <div class="ml-6">
        <h2 class="text-3xl font-bold text-gray-900">
          {{ $lead->first_name }} {{ $lead->last_name }}
        </h2>
        <p class="text-gray-600 mt-1">
          Lead ID: {{ $lead->id }} | Created: {{ $lead->created_at->format('M d, Y H:ia') }}
        </p>
      </div>
    </div>

    {{-- Source & Type badges --}}
    <div class="flex items-center space-x-3">
      <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
        {{ $lead->source }}
      </span>
      <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
        {{ ucfirst($lead->type) }}
      </span>
    </div>
  </div>

  {{-- Details Grid --}}
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

    {{-- Phone --}}
    <div>
      <p class="text-sm text-gray-500">Phone</p>
      <p class="font-medium">
        {{ preg_replace(
             '/^(\d{3})(\d{3})(\d{4})$/',
             '($1)$2-$3',
             preg_replace('/\D/', '', $lead->phone)
           ) }}
      </p>
    </div>

    {{-- Email --}}
    <div>
      <p class="text-sm text-gray-500">Email</p>
      <p class="font-medium">{{ $lead->email ?? 'Not provided' }}</p>
    </div>

    {{-- Address --}}
    <div class="md:col-span-2 lg:col-span-1">
      <p class="text-sm text-gray-500">Address</p>
      <p class="font-medium">
        {{ $lead->address }}, {{ $lead->city }}, {{ $lead->state }} {{ $lead->zip }}
      </p>
    </div>

    {{-- Vehicle --}}
    <div>
      <p class="text-sm text-gray-500">Vehicle</p>
      <p class="font-medium">
        {{ $lead->vehicle_year }} {{ $lead->vehicle_make }} {{ $lead->vehicle_model }}
        <span class="block text-xs text-gray-500">{{ $lead->vin ?? '—' }}</span>
      </p>
    </div>

    {{-- Insurance --}}
    <div>
      <p class="text-sm text-gray-500">Insurance</p>
      <p class="font-medium">{{ $lead->insurance_company ?? '—' }}</p>
      <p class="text-sm">{{ $lead->coverage_type ?? '' }}</p>
    </div>

  </div>
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
  <!-- Left Column -->
  <div class="lg:col-span-2 space-y-8">
    <!-- Contact Information -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
      <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
        <h3 class="text-lg font-semibold text-white flex items-center">
          <svg class="w-5 h-5 mr-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7a4 4 0 018 0v1H8V7zM4 15v-2a4 4 0 014-4h8a4 4 0 014 4v2" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
          </svg>
          Contact Information
        </h3>
      </div>
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <p class="text-sm text-gray-500">Phone</p>
            <p class="font-medium">{{ $lead->phone }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500">Email</p>
            <p class="font-medium">{{ $lead->email ?? 'Not provided' }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500">Address</p>
            <p class="font-medium">{{ $lead->address }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500">City / Zip</p>
            <p class="font-medium">{{ $lead->city }} {{ $lead->zip_code }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Vehicle Information -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
      <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
        <h3 class="text-lg font-semibold text-white flex items-center">
          <svg class="w-5 h-5 mr-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13h2l.4 2m0 0h13.2l.4-2m-14 0L5 6h14l1 7m-5 6a2 2 0 11-4 0" />
          </svg>
          Vehicle Information
        </h3>
      </div>
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div>
            <p class="text-sm text-gray-500">Year</p>
            <p class="font-semibold">{{ $lead->vehicle_year }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500">Make</p>
            <p class="font-semibold">{{ $lead->vehicle_make }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500">Model</p>
            <p class="font-semibold">{{ $lead->vehicle_model }}</p>
          </div>
          <div class="lg:col-span-3">
            <p class="text-sm text-gray-500">VIN</p>
            <p class="font-semibold text-xs">{{ $lead->vin ?? 'Not provided' }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Right Column -->
  <div class="space-y-8">
    <!-- Current Insurance -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
      <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 px-6 py-4">
        <h3 class="text-lg font-semibold text-white flex items-center">
          <svg class="w-5 h-5 mr-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m4 4h-1v-4h-1m2-4a4 4 0 00-8 0v4a4 4 0 008 0V8z" />
          </svg>
          Current Insurance
        </h3>
      </div>
      <div class="p-6">
        <div class="space-y-4">
          <div>
            <p class="text-sm text-gray-500">Insurance Company</p>
            <p class="font-semibold text-indigo-900">{{ $lead->insurance_company }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500">Coverage Type</p>
            <p class="font-semibold text-indigo-900">{{ $lead->coverage_type }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
      <div class="bg-gradient-to-r from-gray-500 to-gray-600 px-6 py-4">
        <h3 class="text-lg font-semibold text-white flex items-center">
          <svg class="w-5 h-5 mr-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Actions
        </h3>
      </div>
      <div class="p-6 space-y-3">
        <a href="/leads/{{ $lead->id }}/edit" class="w-full bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 transition duration-200 flex items-center justify-center">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11c0 .55.45 1 1 1h11a2 2 0 002-2v-5" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.121 2.121 0 113 3L12 15l-4 1 1-4 9.5-9.5z" />
          </svg>
          Edit Lead
        </a>
        <a href="/leads" class="w-full bg-gray-600 text-white px-4 py-3 rounded-lg hover:bg-gray-700 transition duration-200 flex items-center justify-center">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back to List
        </a>
        <form action="/leads/{{ $lead->id }}" method="POST" onsubmit="return confirm('Delete this lead?')">
          <input type="hidden" name="_method" value="DELETE" />
          <input type="hidden" name="_token" value="{{ csrf_token() }}" />
          <button type="submit" class="w-full bg-red-600 text-white px-4 py-3 rounded-lg hover:bg-red-700 transition duration-200 flex items-center justify-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4a2 2 0 012 2v2H8V5a2 2 0 012-2z" />
            </svg>
            Delete Lead
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection 