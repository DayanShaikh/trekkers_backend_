@component('mail::message')
# Hello!

Your six-digit PIN is <h4>{{$token}}</h4>
<p>Please do not share your token With Anyone. You made a request to reset your password. Please discard if this wasn't you.</p>

Thanks,<br>
{{ config('app.name') }}
@endcomponent