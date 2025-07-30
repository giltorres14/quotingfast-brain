<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SmsMessage;

class MessageController extends Controller
{
    public function index()
    {
        $messages = SmsMessage::latest()->take(10)->get();
        return view('messages.index', compact('messages'));
    }
} 