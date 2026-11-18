<?php

namespace App\Domain\Enums;

enum OwnerActionType: string
{
    case CheckInConfirmed = 'checkin_confirmed';
    case CheckedIn = 'checked_in';
    case Paused = 'paused';
    case Resumed = 'resumed';
}
