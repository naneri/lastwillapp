<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: sans-serif; color: #212529; max-width: 540px; margin: 0 auto; padding: 24px;">

@if ($stage === \App\Domain\Enums\CheckInStage::Monthly)
    <p>Hi {{ $owner->name }},</p>

    <p>
        This is your regular check-in from {{ config('app.name') }}. Click the
        button below to confirm you're OK — this keeps your stored information
        from being shared with your contacts.
    </p>
@elseif ($stage === \App\Domain\Enums\CheckInStage::AtRisk)
    <p>Hi {{ $owner->name }},</p>

    <p>
        We didn't hear back from your last check-in. Please confirm you're OK
        within the next 7 days — if we don't hear from you, we'll start
        checking in daily.
    </p>
@else
    <p style="color: #b02a37; font-weight: bold;">This needs your attention today.</p>

    <p>Hi {{ $owner->name }},</p>

    <p>
        You have not confirmed several check-ins in a row. If you don't confirm
        today, we will move one step closer to sharing your stored information
        with your emergency contacts. If that's not what you want, please
        confirm now.
    </p>
@endif

<p style="margin: 32px 0;">
    <a href="{{ $confirmUrl }}"
       style="background: #1b1b18; color: #fff; padding: 12px 20px; text-decoration: none; border-radius: 4px; display: inline-block;">
        I'm OK — confirm now
    </a>
</p>

<p style="color: #6c757d; font-size: 13px;">
    This link expires {{ $expiresAt->toDayDateTimeString() }}. If you're not
    expecting this email, you can ignore it.
</p>

</body>
</html>
