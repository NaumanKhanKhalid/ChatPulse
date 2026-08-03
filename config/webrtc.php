<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ICE servers
    |--------------------------------------------------------------------------
    |
    | STUN is enough for most calls and needs no account. TURN is only used
    | when a direct peer connection cannot be established (strict NAT,
    | corporate firewalls, some mobile carriers) — roughly 10-20% of calls.
    |
    | To enable TURN, set these in .env:
    |   TURN_URLS=turn:your-host:3478,turns:your-host:5349
    |   TURN_USERNAME=...
    |   TURN_CREDENTIAL=...
    |
    | Works with a self-hosted coturn server or a provider such as Metered.
    | Leave TURN_URLS empty and calls fall back to STUN only.
    |
    */

    'stun_urls' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('STUN_URLS', 'stun:stun.l.google.com:19302,stun:stun1.l.google.com:19302'))
    ))),

    'turn_urls' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('TURN_URLS', ''))
    ))),

    'turn_username'   => env('TURN_USERNAME'),
    'turn_credential' => env('TURN_CREDENTIAL'),

];
