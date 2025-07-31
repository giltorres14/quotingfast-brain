@extends('layouts.app')

@section('content')
<div class="p-4 bg-gray-100">
  <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
    <h1 class="text-2xl font-semibold mb-4">Create New Lead</h1>

    <form action="{{ route('leads.store') }}" method="POST" class="space-y-4">
      @csrf

      <div>
        <label class="block text-sm font-medium">First Name</label>
        <input name="first_name" type="text" class="mt-1 block w-full border rounded px-3 py-2" required>
      </div>

      <div>
        <label class="block text-sm font-medium">Last Name</label>
        <input name="last_name" type="text" class="mt-1 block w-full border rounded px-3 py-2" required>
      </div>

      <div>
        <label class="block text-sm font-medium">Phone</label>
        <input name="phone" type="text" class="mt-1 block w-full border rounded px-3 py-2" required>
      </div>

      <div>
        <label class="block text-sm font-medium">Email</label>
        <input name="email" type="email" class="mt-1 block w-full border rounded px-3 py-2">
      </div>

      <div class="flex justify-end">
        <a href="{{ route('leads.index') }}" class="px-4 py-2 mr-2 bg-gray-200 rounded">Cancel</a>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Save Lead</button>
      </div>
    </form>
  </div>
</div>
@endsection 