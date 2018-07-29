funtask
===============

yet another multitasking framework base swoole

### env

- PHP-5.6.31
- Swoole-1.10.5

### install

```bash
wget http://pecl.php.net/get/swoole-1.10.5.tgz
tar xf swoole-1.10.5.tgz
cd swoole-1.10.5
phpize

# Swoole debug (optional)
./configure --enable-swoole-debug
make && make install
```

vi /your-path/php.ini
```vim
extension=swoole
```

### install test

```bash
php -m | grep swoole
php --ri swoole
```

### autoload

```bash
composer install
```

### config

cp .env.example .env

```dotenv
# APP
APP_DEBUG=true          # debug mod will print log or not
APP_LOG_PATH='/data/logs/funtask.log'

# REDIS
REDIS_HOST='127.0.0.1'
REDIS_PORT=6379
REDIS_DATABASE=0
REDIS_PASSWORD=
REDIS_POOL_NUM=3

# PROCESS
PROCESS_NAME='funtask:process:'
DAEMON=false
MAX_EXECUTE_TIME=3600   # worker max execute time, second
MIN_WORKER_NUM=3 
MAX_WORKER_NUM=6
TICKER=60000            # masker ticking, microsecond
PAUSE_TIME=3            # pause time when worker rob key of queue, second
MAX_QUEUE_NUM=10        # starting expand process when queue is overflow
```

### start

```bash
php your-path/funtask/bin/funtask
```

### stop

```bash
bash your-path/funtask/bin/stop.sh funtask
```

### moniter

use supervisor

```dotenv
# PROCESS
DAEMON=false
```