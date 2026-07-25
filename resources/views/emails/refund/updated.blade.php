@component('mail::message')
# Refund Updated

The status of Refund ID: {{ $refund->id }} for Order #{{ $refund->order->tracking_number }} has been updated to {{ $refund->status }}.

[View Refund]({{ $url }})

Thanks,<br>
{{ config('app.name') }}
@endcomponent