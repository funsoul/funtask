<?php
namespace App\Task;

use Funsoul\Funtask\Service\MySQLDriver;
use Funsoul\Funtask\Service\RedisPool;
use Funsoul\Funtask\Service\Logger;
use Funsoul\Funtask\Service\Config;
use Funsoul\Funtask\Task\Base as Task;

class FunTask implements Task
{
    public static function getData()
    {
        return 1;
    }

    public static function run($params)
    {
        Logger::info($params);
    }
}