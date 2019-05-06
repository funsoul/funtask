<?php declare(strict_types=1);

namespace Funsoul\Funtask\Process;

use Swoole\Process\Pool;

/**
 * swoole process pool wrapper
 *
 * Class ProcessPool
 *
 * @package Funsoul\Funtask
 */
class ProcessPool extends Base
{
    /** @var Pool */
    private $pool;

    /** @var string */
    private $workerName = 'Worker';

    /** @var int */
    private $workerNum = 3;

    /** @var JobInterface */
    private $job;

    /** @var callable */
    private $workerStopCallback = null;

    /**
     * @param string $workerName
     * @return ProcessPool
     */
    public function setWorkerName(string $workerName): ProcessPool
    {
        $this->workerName = $workerName;
        return $this;
    }

    /**
     * @param int $workerNum
     * @return ProcessPool
     */
    public function setWorkerNum(int $workerNum): ProcessPool
    {
        $this->workerNum = $workerNum;
        return $this;
    }

    /**
     * @param JobInterface $job
     * @return ProcessPool
     */
    public function setJob(JobInterface $job): ProcessPool
    {
        $this->job = $job;
        return $this;
    }

    /**
     * @param callable $workerStopCallback
     * @return ProcessPool
     */
    public function setWorkerStopCallback(callable $workerStopCallback): ProcessPool
    {
        $this->workerStopCallback = $workerStopCallback;
        return $this;
    }

    /**
     * ProcessPool constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->beforeStart();

        if ($this->workerNum <= 0)
            $this->workerNum = 3;

        $this->pool = new Pool($this->workerNum);

        $masterPid = getmypid();

        $this->pool->on("WorkerStart", function ($pool, $workerId) use ($masterPid) {
            $this->workerStart($workerId, $masterPid);
        });

        $this->pool->on("WorkerStop", function ($pool, $workerId) {
           $this->workerStop($workerId);
        });
    }

    /**
     * @param int $workerId
     * @param int $masterPid
     *
     * @throws \Exception
     */
    private function workerStart($workerId = 0, $masterPid = 0)
    {
        $running = true;

        $pid = getmypid();

        $this->setProcessName("{$this->workerName}[{$pid}]#{$workerId}");

        while ($running) {

            if ($this->isMasterKilled($masterPid)) {
                $running = false;

                $this->job->handle();
            } else {
                $running = $this->job->handle();
            }
        }
    }

    /**
     * @param int $workerId
     * @return mixed
     */
    private function workerStop($workerId = 0)
    {
        if (is_callable($this->workerStopCallback))
            return $this->workerStopCallback($workerId);
        return $workerId;
    }

    public function start()
    {
        return $this->pool->start();
    }
}