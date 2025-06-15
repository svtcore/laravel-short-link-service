<?php

return [
    'secret' => env('MAINTENANCE_SECRET'),
    'redirect' => env('MAINTENANCE_REDIRECT', '/admin/settings'),
    'retry' => env('MAINTENANCE_RETRY', 60),
    'status' => env('MAINTENANCE_STATUS', 503),
    'message' => env('MAINTENANCE_MESSAGE', 'Site under maintenance'),
];
