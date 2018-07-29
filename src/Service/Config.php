<?php

namespace Funsoul\Funtask\Service;

class Config
{
    private static $_config = null;

    public static function get($configName)
    {
        if(! isset(static::$_config[$configName])){
            $configFile = FUN_TASK_APP_PATH . "/Config/{$configName}.php";
            if(file_exists($configFile)){
                static::$_config[$configName] = require_once $configFile;
            }else{
                throw new \Exception("config file[{$configFile}] not exists");
            }
        }
        return static::$_config[$configName];
    }
}