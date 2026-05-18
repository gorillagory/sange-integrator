<?php

return [
    'enabled' => env('AUDIT_ENABLED', true),
    'fallback_log_channel' => env('AUDIT_FALLBACK_LOG_CHANNEL', 'stack'),
];
