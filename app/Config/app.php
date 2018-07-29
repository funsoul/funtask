<?php

return [
    'app_debug' => getenv('APP_DEBUG'),
    'log' => [
        'file' => getenv('APP_LOG_PATH')
    ]
];