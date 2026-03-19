@component('mail::message')
# 📄 Document Shared

Hello,

<b style="color:#2d3748;">{{ $sharedBy->name }}</b> has shared a document with you.

@component('mail::panel')
<strong>📁 Document:</strong><br>
Document: {{ $document->name }}
@endcomponent

@component('mail::button', ['url' =>  route('login')])
🔍 Open Document
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent