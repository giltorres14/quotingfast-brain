@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-4">Onboarding: {{ $client->name }}</h1>

    @if(session('status'))
        <div class="bg-green-200 text-green-800 p-4 rounded mb-4">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white shadow rounded p-6 mb-6">
        <h2 class="text-xl font-semibold mb-2">Onboarding Agreement</h2>
        <p>Please review and accept the terms to proceed.</p>
        <!-- Add agreement text here -->
    </div>

    <form method="POST" action="{{ route('onboarding.send', $client) }}">
        @csrf
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
            Send Welcome Email
        </button>
    </form>
</div>
@endsection 