<?php

return [
    'name' => getenv('PROCESS_NAME'),
    'is_daemon' => getenv('DAEMON'),
    'max_execute_time' => getenv('MAX_EXECUTE_TIME'),
    'pause_time' => getenv('PAUSE_TIME'),
    'min_worker_num' => getenv('MIN_WORKER_NUM'),
    'max_worker_num' => getenv('MAX_WORKER_NUM'),
    'max_queue_num' => getenv('MAX_QUEUE_NUM'),
    'ticker' => getenv('TICKER'),
    'status' => [
        'active' => 1, // 活跃
        'idle'   => 2, // 闲置
    ]
];