@component('mail::message')
# Order Cancelled

Your order #{{ $order->tracking_number }} has been cancelled.

[View Order]({{ $url }})

Thanks,<br>
{{ config('app.name') }}
@endcomponent