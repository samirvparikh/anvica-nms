<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stale device threshold (minutes)
    |--------------------------------------------------------------------------
    |
    | If a device has not posted to /api/device/data within this many minutes,
    | it is marked Down and a Device Down alert is created.
    |
    */
    'stale_after_minutes' => (int) env('DEVICE_STALE_AFTER_MINUTES', 5),
];
