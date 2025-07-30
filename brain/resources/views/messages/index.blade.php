@extends('layouts.app')

@section('content')
    <h2 class="text-2xl font-semibold mb-4">Messaging</h2>
    <ul>
        @foreach($messages as $msg)
            <li>{{ $msg->from }} → {{ $msg->body }}</li>
        @endforeach
    </ul>
@endsection 