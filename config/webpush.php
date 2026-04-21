<?php

return [
    'vapid' => [
        'subject' => env('WEBPUSH_VAPID_SUBJECT'),
        'public_key' => env('WEBPUSH_VAPID_PUBLIC_KEY'),
        'private_key' => env('WEBPUSH_VAPID_PRIVATE_KEY'),
    ],
    'verify_ssl' => env('WEBPUSH_VERIFY_SSL', true),
];
