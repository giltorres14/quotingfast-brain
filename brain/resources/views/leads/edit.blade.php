@extends('layouts.app')

@section('content')
<div class="py-8">
  <div class="max-w-4xl mx-auto bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
      <h3 class="text-lg font-medium leading-6 text-gray-900">Lead Details</h3>
    </div>
    <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
      <dl class="sm:divide-y sm:divide-gray-200">
        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
          <dt class="text-sm font-medium text-gray-500">Name</dt>
          <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $lead->name }}</dd>
        </div>
        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
          <dt class="text-sm font-medium text-gray-500">Email</dt>
          <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $lead->email }}</dd>
        </div>
        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
          <dt class="text-sm font-medium text-gray-500">Phone</dt>
          <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $lead->phone }}</dd>
        </div>
        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
          <dt class="text-sm font-medium text-gray-500">Address</dt>
          <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $lead->address }}</dd>
        </div>
        <!-- add more fields here as needed -->
      </dl>
    </div>
  </div>
</div>
<div class="container mx-auto px-4 py-8">
  <!-- Page Header -->
  <div class="flex justify-between items-center mb-8">
    <div>
      <h1 class="text-3xl font-bold text-gray-900">Edit Lead</h1>
      <p class="text-gray-600 mt-2">Modify lead information</p>
    </div>
    <div class="flex space-x-3">
      <a href="{{ route('leads.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">
        Back to Leads
      </a>
      <button type="submit" form="edit-lead-form" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
        Save
      </button>
    </div>
  </div>

  <form id="edit-lead-form" action="{{ route('leads.update', $lead) }}" method="POST">
    @csrf
    @method('PUT')

    {{-- Lead Header Card --}}
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8 flex items-center justify-between">
      <div class="flex items-center">
        {{-- Avatar: first letter of type, uppercase --}}
        <div class="h-16 w-16 rounded-full flex items-center justify-center text-white text-2xl font-bold"
             style="background-color: {{ $lead->type === 'internet' ? '#93c5fd' /*light blue*/ : '#ccc' }};">
          {{ strtoupper(substr($lead->type, 0, 1)) }}
        </div>
        <div class="ml-6">
          <h2 class="text-3xl font-bold text-gray-900">
            {{ $lead->first_name }} {{ $lead->last_name }}
          </h2>
          <p class="text-gray-600 mt-1">
            Lead ID: {{ $lead->id }} | Created: {{ $lead->created_at->format('M d, Y H:ia') }}
          </p>
        </div>
      </div>
      <div class="flex items-center space-x-3">
        {{-- Source bubble --}}
        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
          {{ $lead->source }}
        </span>
        {{-- Type bubble --}}
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
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Left Column -->
      <div class="lg:col-span-2 space-y-8">
        <!-- Contact Information -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
          <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
            <h3 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-3 text-white"></svg>
              Contact Information
            </h3>
          </div>
          <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="text-sm text-gray-500" for="phone">Phone</label>
              <input type="text" id="phone" name="phone" value="{{ old('phone', $lead->phone) }}" class="mt-1 block w-full rounded border-gray-300 shadow-sm" />
            </div>
            <div>
              <label class="text-sm text-gray-500" for="email">Email</label>
              <input type="email" id="email" name="email" value="{{ old('email', $lead->email) }}" class="mt-1 block w-full rounded border-gray-300 shadow-sm" />
            </div>
            <div>
              <label class="text-sm text-gray-500" for="address">Address</label>
              <input type="text" id="address" name="address" value="{{ old('address', $lead->address) }}" class="mt-1 block w-full rounded border-gray-300 shadow-sm" />
            </div>
            <div>
              <label class="text-sm text-gray-500" for="city">City</label>
              <input type="text" id="city" name="city" value="{{ old('city', $lead->city) }}" class="mt-1 block w-full rounded border-gray-300 shadow-sm" />
              <label class="text-sm text-gray-500" for="state">State</label>
              <input type="text" id="state" name="state" value="{{ old('state', $lead->state) }}" class="mt-1 block w-full rounded border-gray-300 shadow-sm" />
              <label class="text-sm text-gray-500" for="zip">Zip</label>
              <input type="text" id="zip" name="zip" value="{{ old('zip', $lead->zip_code) }}" class="mt-1 block w-full rounded border-gray-300 shadow-sm" />
            </div>
          </div>
        </div>
        <!-- Vehicle Information -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
          <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
            <h3 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-3 text-white"></svg>
              Vehicle Information
            </h3>
          </div>
          <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
              <label class="text-sm text-gray-500" for="vehicle_year">Year</label>
              <input type="number" id="vehicle_year" name="vehicle_year" value="{{ old('vehicle_year', $lead->vehicle_year) }}" class="mt-1 block w-full rounded border-gray-300 shadow-sm" />
            </div>
            <div>
              <label class="text-sm text-gray-500" for="vehicle_make">Make</label>
              <input type="text" id="vehicle_make" name="vehicle_make" value="{{ old('vehicle_make', $lead->vehicle_make) }}" class="mt-1 block w-full rounded border-gray-300 shadow-sm" />
            </div>
            <div>
              <label class="text-sm text-gray-500" for="vehicle_model">Model</label>
              <input type="text" id="vehicle_model" name="vehicle_model" value="{{ old('vehicle_model', $lead->vehicle_model) }}" class="mt-1 block w-full rounded border-gray-300 shadow-sm" />
            </div>
            <div class="lg:col-span-3">
              <label class="text-sm text-gray-500" for="vin">VIN</label>
              <input type="text" id="vin" name="vin" value="{{ old('vin', $lead->vin) }}" class="mt-1 block w-full rounded border-gray-300 shadow-sm text-xs" />
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
              <svg class="w-5 h-5 mr-3 text-white"></svg>
              Current Insurance
            </h3>
          </div>
          <div class="p-6 space-y-4">
            <div>
              <label class="text-sm text-gray-500" for="insurance_company">Insurance Company</label>
              <input type="text" id="insurance_company" name="insurance_company" value="{{ old('insurance_company', $lead->insurance_company) }}" class="mt-1 block w-full rounded border-gray-300 shadow-sm text-indigo-900" />
            </div>
            <div>
              <label class="text-sm text-gray-500" for="coverage_type">Coverage Type</label>
              <input type="text" id="coverage_type" name="coverage_type" value="{{ old('coverage_type', $lead->coverage_type) }}" class="mt-1 block w-full rounded border-gray-300 shadow-sm text-indigo-900" />
            </div>
          </div>
        </div>
        <!-- Dates -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
          <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 px-6 py-4">
            <h3 class="text-lg font-semibold text-white flex items-center">
              <svg class="w-5 h-5 mr-3 text-white"></svg>
              Dates
            </h3>
          </div>
          <div class="p-6 space-y-4">
            <div>
              <label class="text-sm text-gray-500" for="join_date">Join Date</label>
              <input type="date" id="join_date" name="join_date" value="{{ old('join_date', $lead->join_date) }}" class="mt-1 block w-full rounded border-gray-300 shadow-sm" />
            </div>
            <div>
              <label class="text-sm text-gray-500" for="received_date">Received Date</label>
              <input type="date" id="received_date" name="received_date" value="{{ old('received_date', $lead->received_date) }}" class="mt-1 block w-full rounded border-gray-300 shadow-sm" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>
@endsection 