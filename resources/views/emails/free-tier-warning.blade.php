<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: sans-serif; color: #212529; max-width: 540px; margin: 0 auto; padding: 24px;">

<p>Hi {{ $owner->name }},</p>

<p>
    You've been on {{ config('app.name') }}'s free plan for a while now.
    Your free period ends on <strong>{{ $endsAt->toFormattedDateString() }}</strong> —
    after that, we'll stop sending check-ins and won't be able to share your
    information with your trusted contact if you go silent, until you
    subscribe to a paid plan.
</p>

<p style="margin: 32px 0;">
    <a href="{{ route('billing.show') }}"
       style="background: #1b1b18; color: #fff; padding: 12px 20px; text-decoration: none; border-radius: 4px; display: inline-block;">
        View plans
    </a>
</p>

<p style="color: #6c757d; font-size: 13px;">
    Nothing you've saved will be deleted — subscribing any time picks
    monitoring right back up.
</p>

</body>
</html>
