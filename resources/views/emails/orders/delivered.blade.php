@component('mail::message')
# Order Delivered

Your order #{{ $order->tracking_number }} has been delivered.

[View Order]({{ $url }})

Thanks,<br>
{{ config('app.name') }}
@endcomponent