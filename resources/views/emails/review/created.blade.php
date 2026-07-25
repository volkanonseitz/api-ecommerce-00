@component('mail::message')
# New Review Created

A new review has been submitted for product {{ $review->product->name }}.

[View Product]({{ $url }})

Thanks,<br>
{{ config('app.name') }}
@endcomponent