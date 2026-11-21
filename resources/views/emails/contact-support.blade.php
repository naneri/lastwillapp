<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: sans-serif; color: #212529; max-width: 540px; margin: 0 auto; padding: 24px;">

<p>From: {{ $user->name }} ({{ $user->email }})</p>

<p style="white-space: pre-wrap;">{{ $body }}</p>

</body>
</html>
