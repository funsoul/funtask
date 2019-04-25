<?php declare(strict_types=1);

namespace Funsoul\Funtask;

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
    /** @var int pause second */
    private $workerPauseSec= 0;

    /** @var Pool */
    private $pool;

    /** @var string */
    private $workerName = 'Worker';

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
     * @param int $workerPauseSec
     * @return ProcessPool
     */
    public function setWorkerPauseSec(int $workerPauseSec): ProcessPool
    {
        $this->workerPauseSec = $workerPauseSec;
        return $this;
    }

    /**
     * ProcessPool constructor.
     *
     * @param JobInterface $job
     * @param int $workerNum
     * @param string $workerName
     * @param int $pauseSecond
     *
     * @throws \Exception
     */
    public function __construct(JobInterface $job, int $workerNum = 3, string $workerName = 'Worker', int $pauseSecond = 1)
    {
        $this->beforeStart();

        $this->workerName = $workerName;
        $this->workerPauseSec = $pauseSecond;

        $this->pool = new Pool($workerNum);

        $masterPid = getmypid();

        $this->pool->on("WorkerStart", function ($pool, $workerId) use ($masterPid, $job) {
            $this->workerStart($workerId, $masterPid, $job);
        });

        $this->pool->on("WorkerStop", function ($pool, $workerId) {
           $this->workerStop($workerId);
        });
    }

    /**
     * @param int $workerId
     * @param int $masterPid
     * @param JobInterface $job
     *
     * @throws \Exception
     */
    private function workerStart($workerId = 0, $masterPid = 0, JobInterface $job)
    {
        $running = true;

        $pid = getmypid();

        $this->setProcessName("{$this->workerName}[{$pid}]#{$workerId}");

        while ($running) {

            if ($this->isMasterKilled($masterPid)) {
                $running = false;

                $job->handle();
            } else {
                $running = $job->handle();

                sleep($this->workerPauseSec);
            }
        }
    }

    /**
     * @param int $workerId
     */
    private function workerStop($workerId = 0)
    {

    }

    public function start()
    {
        return $this->pool->start();
    }
}