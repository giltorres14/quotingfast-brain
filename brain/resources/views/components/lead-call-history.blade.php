{{-- Lead Call History Component --}}
@props(['lead'])

@php
    $callMetrics = $lead->viciCallMetrics;
    $hasCallHistory = $callMetrics && $callMetrics->call_attempts > 0;
@endphp

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
    <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
        </svg>
        Call History
    </h3>
    
    @if($hasCallHistory)
        {{-- Summary Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <div class="bg-gray-50 rounded p-2">
                <div class="text-xs text-gray-500">Total Calls</div>
                <div class="text-lg font-semibold text-gray-900">{{ $callMetrics->call_attempts }}</div>
            </div>
            
            <div class="bg-gray-50 rounded p-2">
                <div class="text-xs text-gray-500">Talk Time</div>
                <div class="text-lg font-semibold text-gray-900">
                    @if($callMetrics->talk_time)
                        {{ gmdate('i:s', $callMetrics->talk_time) }}
                    @else
                        --
                    @endif
                </div>
            </div>
            
            <div class="bg-gray-50 rounded p-2">
                <div class="text-xs text-gray-500">Last Agent</div>
                <div class="text-sm font-medium text-gray-900">{{ $callMetrics->agent_id ?? 'N/A' }}</div>
            </div>
            
            <div class="bg-gray-50 rounded p-2">
                <div class="text-xs text-gray-500">Disposition</div>
                <div class="text-sm font-medium">
                    @php
                        $dispositionColors = [
                            'SALE' => 'text-green-600',
                            'XFER' => 'text-blue-600',
                            'CALLBK' => 'text-yellow-600',
                            'NI' => 'text-red-600',
                            'DNC' => 'text-red-700',
                            'default' => 'text-gray-600'
                        ];
                        $color = $dispositionColors[$callMetrics->disposition] ?? $dispositionColors['default'];
                    @endphp
                    <span class="{{ $color }}">{{ $callMetrics->disposition ?? 'PENDING' }}</span>
                </div>
            </div>
        </div>
        
        {{-- Call Timeline --}}
        <div class="border-t pt-3">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Call Timeline</h4>
            
            @if($callMetrics->call_history && is_array($callMetrics->call_history))
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    @foreach(array_reverse($callMetrics->call_history) as $call)
                        <div class="flex items-start space-x-3 text-sm">
                            <div class="flex-shrink-0 w-2 h-2 bg-blue-400 rounded-full mt-1.5"></div>
                            <div class="flex-1">
                                <div class="flex justify-between">
                                    <span class="text-gray-900">
                                        {{ $call['status'] ?? 'Call' }}
                                        @if(isset($call['agent']))
                                            by {{ $call['agent'] }}
                                        @endif
                                    </span>
                                    <span class="text-gray-500">
                                        @if(isset($call['timestamp']))
                                            {{ \Carbon\Carbon::parse($call['timestamp'])->format('M d, g:i A') }}
                                        @endif
                                    </span>
                                </div>
                                @if(isset($call['talk_time']) && $call['talk_time'] > 0)
                                    <div class="text-gray-500">
                                        Talk time: {{ gmdate('i:s', $call['talk_time']) }}
                                    </div>
                                @endif
                                @if(isset($call['comments']) && $call['comments'])
                                    <div class="text-gray-600 italic">
                                        "{{ $call['comments'] }}"
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex justify-between text-sm">
                    <div>
                        <span class="text-gray-600">First Call:</span>
                        <span class="text-gray-900">
                            {{ $callMetrics->first_call_time ? $callMetrics->first_call_time->format('M d, Y g:i A') : 'N/A' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-600">Last Call:</span>
                        <span class="text-gray-900">
                            {{ $callMetrics->last_call_time ? $callMetrics->last_call_time->format('M d, Y g:i A') : 'N/A' }}
                        </span>
                    </div>
                </div>
            @endif
        </div>
        
        {{-- Transfer Status --}}
        @if($callMetrics->transfer_requested)
            <div class="mt-3 p-2 bg-blue-50 rounded">
                <div class="flex items-center text-sm">
                    <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                    <span class="text-blue-900">
                        Transfer to {{ $callMetrics->transfer_destination ?? 'buyer' }}
                        @if($callMetrics->transfer_status)
                            - {{ $callMetrics->transfer_status }}
                        @endif
                    </span>
                </div>
            </div>
        @endif
        
    @else
        {{-- No Call History --}}
        <div class="text-center py-6 text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
            </svg>
            <p class="text-sm">No call history yet</p>
            <p class="text-xs text-gray-400 mt-1">Call data will appear here once the lead is contacted</p>
        </div>
    @endif
</div>


