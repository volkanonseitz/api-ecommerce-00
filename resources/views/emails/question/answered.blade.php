@component('mail::message')
# Question Answered

Your question regarding product {{ $question->product->name }} has been answered.

[View Product]({{ $url }})

Thanks,<br>
{{ config('app.name') }}
@endcomponent