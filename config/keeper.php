<?php

return [
    // Owner check-in lifecycle (SPEC.md §4).
    'checkin' => [
        // How long an Active owner can go since their last confirmation
        // before the next monthly check-in is sent.
        'interval_days' => 30,

        // How long a sent monthly check-in stays open before an owner who
        // hasn't confirmed moves from Active to At Risk.
        'confirm_days' => 3,

        // How long the At Risk retry check-in stays open before an owner
        // who hasn't confirmed moves to Daily Retry.
        'at_risk_days' => 4,

        // How many consecutive daily check-in emails are sent in Daily
        // Retry — one per day — before the owner is declared Disappeared.
        'daily_retry_days' => 7,

        // Days between each Daily Retry check-in email.
        'daily_retry_cadence_days' => 1,
    ],

    // Reveal / escalation lifecycle after an owner is Disappeared
    // (SPEC.md §5).
    'escalation' => [
        // How long each will receiver has to confirm a reveal before it
        // escalates to the next one in rank order.
        'confirm_days' => 3,
    ],
];
