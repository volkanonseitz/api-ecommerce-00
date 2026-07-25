@component('mail::message')
# {{ __('payment.failed_subject', ['ORDER_TRACKING_NUMBER' => $order->tracking_number]) }}

{{ __('payment.failed_message', ['ORDER_TRACKING_NUMBER' => $order->tracking_number]) }}

@component('mail::button', ['url' => $url ])
    {{__('payment.view_order')}}
@endcomponent

{{__('payment.thanks')}},<br>
{{ config('app.name') }}
@endcomponent
