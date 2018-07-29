<?php
namespace Funsoul\Funtask\Service;

use App\Task\FunTask;

class ProcessPool
{
    public $mpid = 0;
    private $_config = [];                            // process config
    private $_process_list = [];                      // pid => process object
    private $_current_num = 3;                        // current num of process list 
    private $_max_execute_time = 3600;                // worker max execute time, second
    private $_max_queue_num = 10;                     // max num of queue item
    private $_pause_time = 1;                         // worker pause time, second
    private $_custom_msg_key = 1;                     // common queue key
    private $_mod = 2 | \swoole_process::IPC_NOWAIT;  // 2 => rob mod | no wait ipc 

    public function __construct()
    {
        $this->_init();
        $this->_masterRunning();
        $this->_registerSignal();
    }

    private function _init()
    {
        $this->_config = Config::get('process');
        if($this->_config['is_daemon'] === 'TRUE' || $this->_config['is_daemon'] === 'true'){
            \swoole_process::daemon();
        }
        $this->_current_num = ! empty($this->_config['min_worker_num']) ? $this->_config['min_worker_num'] : $this->_current_num;
        $this->_max_queue_num = ! empty($this->_config['max_queue_num']) ? $this->_config['max_queue_num'] : $this->_max_queue_num;
        $this->_max_execute_time = ! empty($this->_config['max_execute_time']) ? $this->_config['max_execute_time'] : $this->_max_execute_time;
        $this->_pause_time = ! empty($this->_config['pause_time']) ? $this->_config['pause_time'] : $this->_pause_time;
        $this->_custom_msg_key = ftok(__FILE__, 1);
        $this->mpid = getmypid();
        $this->_setProcessName($this->_config['name'] . 'master:' . $this->mpid);
    }

    /**
     * master running
     */
    private function _masterRunning()
    {
        Logger::info("master running");
        // init process pool
        for($i = 0; $i < $this->_current_num; $i++){
            $this->_createProcess();
        }

        // dispatch task with ticking
        $this->_dispatch();
    }

    /**
     * create process
     */
    private function _createProcess()
    {
        if ($this->_current_num > $this->_config['max_worker_num'])
            return;
        $process = new \swoole_process(array($this, 'workerRunning') , false , 2);// no redirect, frame mod
        $process->useQueue($this->_custom_msg_key, $this->_mod);
        $pid = $process->start();
        $this->_process_list[$pid] = $process;
    }

    /**
     * worker running
     * @param $worker
     */
    public function workerRunning($worker)
    {
        $this->_setProcessName($this->_config['name'] . 'worker:' . $worker->pid);
        Logger::info("worker[{$worker->pid}] started" );

        $beginTime = microtime(true);
        $isRunning = true;
        while($isRunning) {
            $isRunning = $beginTime + $this->_max_execute_time > time() ? true : false;
            sleep($this->_pause_time);

            $msgKey = $worker->pop();

            // return idle status and exit because current num of process bigger than min worker num
            if ( ($msgKey == $this->_config['status']['idle'] && $this->_current_num > $this->_config['min_worker_num']))
                break;

            // master killed and worker exit when finished
            if ($this->_isMasterKilled()) {
                if ($msgKey !== false) {
                    Logger::info("current num of process[{$this->_current_num}] worker[{$worker->pid}] starting consume: {$msgKey}");
                    FunTask::run($msgKey);
                }
                break;
            }

            // no wait ipc，return false when no value
            if ($msgKey === false || $msgKey == $this->_config['status']['idle'])
                continue;

            Logger::info("current num of process[{$this->_current_num}] worker[{$worker->pid}] starting consume: {$msgKey}");
            FunTask::run($msgKey);
        }

        Logger::info("worker[{$worker->pid}] exit" );
        $worker->exit();
    }

    /**
     * master dispatch task with ticking
     */
    private function _dispatch()
    {
        \swoole_timer_tick($this->_config['ticker'], function($timer_id) {
            $msgKey = FunTask::getData();
            if (isset($msgKey) && ! empty($msgKey)) {
                // push one process and all process will share it
                $process = current($this->_process_list);
                $isSuccess = $process->push($msgKey);

                // push failed when queue overflow，and current num of process pool smaller than max worker num
                if(! $isSuccess && $this->_current_num < $this->_config['max_worker_num']) {
                    $this->_logQueueStatus('ERROR', 'queue overflow');
                    $this->_startExpansion();
                    $process = current($this->_process_list);
                    $isPushAgain = $process->push($msgKey);
                    if(! $isPushAgain) {
                        $this->_logQueueStatus('ERROR', 'push again failed');
                    }else{
                        $this->_logQueueStatus('INFO', 'push again success');
                    }
                }
                // starting expand when queue is overflow and current num of process pool is smalled than max worker num
                $statQueue = current($this->_process_list)->statQueue();
                if($statQueue['queue_num'] >= $this->_max_queue_num && $this->_current_num < $this->_config['max_worker_num']) {
                    $this->_startExpansion();
                }
            }
            $this->_logQueueStatus('INFO');
        });
    }

    /**
     * expand process pool
     */
    private function _startExpansion()
    {
        $this->_logQueueStatus('INFO');
        $newProcessNum = $this->_config['max_worker_num'] - $this->_current_num;
        for ($i = 0; $i < $newProcessNum; $i++) {
            $this->_createProcess();
            $this->_current_num ++;
        }
    }

    /**
     * register signal
     */
    private function _registerSignal()
    {
        \swoole_process::signal(SIGCHLD, function($sig) {
            while($ret = \swoole_process::wait(false)) {
                $this->_rebootProcess($ret['pid']);
                Logger::info( "回收子进程[{$ret['pid']}]" );
            }
        });
    }

    /**
     * restart process
     * @param $pid
     */
    public function _rebootProcess($pid)
    {
        $index = array_search($pid, array_keys($this->_process_list));
        if ($index !== false) {
            unset($this->_process_list[$pid]);
            $this->_current_num --;
            $this->_createProcess();
            $this->_current_num ++;
            return;
        }
        Logger::error("restart failed, no this PID[{$pid}]");
    }

    /**
     * check master killed
     * @return bool
     */
    private function _isMasterKilled()
    {
        return \swoole_process::kill($this->mpid, 0) ? false : true;
    }

    /**
     * set process name
     * @param $name
     * @return bool
     * @throws \Exception
     */
    private function _setProcessName($name)
    {
        if (PHP_OS == 'Darwin') {
            return false;
        }
        if (function_exists('cli_set_process_title')) {
            cli_set_process_title($name);
        } else {
            if (function_exists('swoole_set_process_name')) {
                swoole_set_process_name($name);
            } else {
                throw new \Exception(__METHOD__ . "failed, require cli_set_process_title | swoole_set_process_name");
            }
        }
    }

    /**
     * log queue status
     * @param $flag
     * @param null $msg
     */
    private function _logQueueStatus($flag, $msg = null)
    {
        $process = current($this->_process_list);
        if($process == false){
            Logger::info('_process_list is empty');
            return;
        }
        $statQueue = json_encode($process->statQueue());
        switch ($flag){
        case 'INFO':
            Logger::info("statQueue => {$statQueue}");
            break;
        case 'ERROR':
            Logger::error("【{$msg}】 statQueue => {$statQueue}");
        }
    }
}