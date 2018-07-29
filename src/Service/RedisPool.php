<?php
namespace Funsoul\Funtask\Service;

class RedisPool
{
    private $_redis_pool = [];
    private $_db_config;
    private static $_instance = NULL;
    private function __construct(){}

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
            self::$_instance->init();
        }
        return self::$_instance;
    }

    private function init(){
        $this->_db_config = Config::get('database');
        $poolNum = $this->_db_config['REDIS']['POOL_NUM'];
        for ($i = 0; $i < $poolNum; $i++){
            $redis = $this->connect();
            array_push($this->_redis_pool, $redis);
        }
    }

    private function connect(){
        $redis = new \Redis();
        $redis->connect(
            $this->_db_config['REDIS']['HOST'],
            $this->_db_config['REDIS']['PORT']
        );

        if(! empty($this->_db_config['REDIS']['PASSWORD'])) {
            $redis->auth($this->_db_config['REDIS']['PASSWORD']);
        }

        if(! empty($this->_db_config['REDIS']['DATABASE'])) {
            $redis->select($this->_db_config['REDIS']['DATABASE']);
        }

        return $redis;
    }

    public function pop(){
        if(empty($this->_redis_pool)){
            $redis = $this->connect();
            return $redis;
        }

        $redis = array_pop($this->_redis_pool);
        if($redis->ping() == 'PONG') return $redis;

        // expired handle
        unset($redis);

        $redis = $this->connect();
        return $redis;
    }

    public function push($redis){
        if(count($this->_redis_pool) == $this->_db_config['REDIS']['POOL_NUM']){
            // redis pool overflow
            unset($redis);
        }else{
            array_unshift($this->_redis_pool, $redis);
        }
        return true;
    }
}