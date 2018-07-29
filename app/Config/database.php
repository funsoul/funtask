<?php
return [
    'MYSQL' => [
        'db_gm_ljh5' => [
            'HOST'     => getenv('LJH5_DB_HOST'),
            'USER'     => getenv('LJH5_DB_USER'),
            'PASSWORD' => getenv('LJH5_DB_PASSWORD'),
            'DB_NAME'  => getenv('LJH5_DB_NAME'),
            'TABLE'    => getenv('LJH5_DB_TABLE')
        ]
    ],
    'REDIS' => [
        'HOST' => getenv('REDIS_HOST'),
        'PORT' => getenv('REDIS_PORT'),
        'DATABASE' => getenv('REDIS_DATABASE'),
        'PASSWORD' => getenv('REDIS_PASSWORD'),
        'POOL_NUM' => getenv('REDIS_POOL_NUM')
    ]
];
