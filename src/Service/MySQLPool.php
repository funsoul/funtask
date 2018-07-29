<?php

namespace Funsoul\Funtask\Service;

class MySQLPool
{
    private $_mysql_pool = [];
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
        $poolNum = isset($this->_db_config['MYSQL']['POOL_NUM']) ? $this->_db_config['MYSQL']['POOL_NUM'] : 3;
        for ($i = 0; $i < $poolNum; $i++){
            $handle = $this->connect(
                $this->_db_config['MYSQL']['HOST'],
                $this->_db_config['MYSQL']['USER'],
                $this->_db_config['MYSQL']['PASSWORD'],
                $this->_db_config['MYSQL']['DB']
            );
            if($handle !== false){
                array_push($this->_mysql_pool, $handle);
            }
        }
    }

    private function connect($host, $user, $psw, $db){
        $con = mysqli_connect($host,$user,$psw,$db);
        if (mysqli_connect_errno($con))
        {
            throw new \Exception("Failed to connect to MySQL: " . mysqli_connect_error());
        }
        return $con;
    }

    public function pop(){
        if(empty($this->_mysql_pool)){
            $handle = $this->connect(
                $this->_db_config['MYSQL']['HOST'],
                $this->_db_config['MYSQL']['USER'],
                $this->_db_config['MYSQL']['PASSWORD'],
                $this->_db_config['MYSQL']['DB']
            );
            return $handle;
        }

        $handle = array_pop($this->_mysql_pool);
        if(mysqli_ping($handle)) return $handle;

        // expired handle
        unset($handle);

        $handle = $this->connect(
            $this->_db_config['MYSQL']['HOST'],
            $this->_db_config['MYSQL']['USER'],
            $this->_db_config['MYSQL']['PASSWORD'],
            $this->_db_config['MYSQL']['DB']
        );
        return $handle;
    }

    public function push($handle){
        if(count($this->_mysql_pool) == $this->_db_config['MYSQL']['POOL_NUM']){
            // mysql pool overflows
            unset($handle);
        }else{
            array_unshift($this->_mysql_pool, $handle);
        }
        return true;
    }
}