<?php

namespace Funsoul\Funtask\Service;

class Logger
{
    private static $_instance = null;
    private $_config = [];

    public function __construct()
    {
        $this->_config = Config::get('app');
    }

    private static function init()
    {
        if(is_null(self::$_instance)){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static function info($msg)
    {
        static::init()->printLog('INFO', $msg);
    }

    public static function error($msg)
    {
        static::init()->printLog('ERROR', $msg);
    }

    private function printLog($type, $msg)
    {
        $trace = debug_backtrace();
        $class = isset($trace[2]['class']) ? $trace[2]['class'] : __CLASS__;
        $func = isset($trace[2]['function']) ? $trace[2]['function'] : __FUNCTION__;
        $msg = "[" . date('Y-m-d H:i:s') . "] TRACE: #{$class}__{$func}() {$type}: {$msg}" . PHP_EOL;
        if($this->_config['app_debug'] === 'true' || $this->_config['app_debug'] === 'TRUE'){
            echo $msg;
        }else{
            $this->fLog($msg);
        }
    }

    private function fLog($msg)
    {
        if (empty($msg)) {
            return false;
        }
        if (! is_string($msg)) {
            $msg = var_export($msg, true);
        }
        error_log($msg, 3, $this->_config['log']['file']);
    }
}