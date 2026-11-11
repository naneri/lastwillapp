<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: sans-serif; color: #212529; max-width: 540px; margin: 0 auto; padding: 24px;">

<p>Hi {{ $willReceiver->name ?: '' }},</p>

<p>
    {{ $owner->name }} ({{ $owner->email }}) set up {{ config('app.name') }}, a
    service that shares some information with you if they miss a number of
    scheduled check-ins. That has now happened, and {{ $owner->name }} listed
    you as someone to contact.
</p>

<p>
    This doesn't necessarily mean anything has happened to them — only that
    they haven't confirmed a check-in recently. You can use the link below to
    view what they left for you, and let us know once you've seen it.
</p>

<p style="margin: 32px 0;">
    <a href="{{ $viewUrl }}"
       style="background: #1b1b18; color: #fff; padding: 12px 20px; text-decoration: none; border-radius: 4px; display: inline-block;">
        View what {{ $owner->name }} left for you
    </a>
</p>

<p style="color: #6c757d; font-size: 13px;">
    This link will keep working whenever you come back to it.
</p>

</body>
</html>
