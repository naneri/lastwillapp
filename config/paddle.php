<?php

return [
    // Toggle between Paddle's sandbox and live environments.
    'sandbox' => env('PADDLE_SANDBOX', true),

    // Used client-side to initialize the Paddle.js checkout overlay.
    'client_token' => env('PADDLE_CLIENT_TOKEN'),

    // Used server-side for API calls (e.g. opening the customer portal).
    'api_key' => env('PADDLE_API_KEY'),

    // Used to verify the "Paddle-Signature" header on incoming webhooks.
    'webhook_secret' => env('PADDLE_WEBHOOK_SECRET'),

    // Paddle price IDs for the paid tiers (SPEC.md §12) — set these once
    // the corresponding products/prices exist in the Paddle dashboard.
    'prices' => [
        'basic' => env('PADDLE_PRICE_BASIC'),
        'pro' => env('PADDLE_PRICE_PRO'),
    ],
];
