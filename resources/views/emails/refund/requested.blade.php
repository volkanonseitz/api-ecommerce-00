@component('mail::message')
# Refund Requested

A refund has been requested for Order #{{ $refund->order->tracking_number }}. Refund ID: {{ $refund->id }}.

[View Refund]({{ $url }})

Thanks,<br>
{{ config('app.name') }}
@endcomponent