<?php

namespace App\Enums;

enum OwnerStatus: string
{
    case Setup = 'setup';
    case Active = 'active';
    case AtRisk = 'at_risk';
    case DailyRetry = 'daily_retry';
    case Disappeared = 'disappeared';
}
