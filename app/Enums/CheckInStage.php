<?php

namespace App\Enums;

enum CheckInStage: string
{
    case Monthly = 'monthly';
    case AtRisk = 'at_risk';
    case DailyRetry = 'daily_retry';
}
