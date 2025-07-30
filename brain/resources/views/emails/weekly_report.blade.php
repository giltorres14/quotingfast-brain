@component('mail::message')
# Weekly Report

## Leads Per Day

@component('mail::table')
| Date | Count |
| :--- | ---: |
@foreach($metrics['leadsPerDay'] as $item)
| {{ $item->date }} | {{ $item->count }} |
@endforeach
@endcomponent

## Source Breakdown

@component('mail::table')
| Source | Count |
| :----- | ---: |
@foreach($metrics['sourceBreakdown'] as $item)
| {{ $item->source }} | {{ $item->count }} |
@endforeach
@endcomponent

**Total Failed Notifications**: {{ $metrics['errorCount'] }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
