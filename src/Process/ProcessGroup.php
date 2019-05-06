<?php declare(strict_types=1);

namespace Funsoul\Funtask\Process;

use Swoole\Process;

/**
 * swoole processes wrapper
 *
 * Unlike a process pool, a process group does not restart the process.
 *
 * Class ProcessGroup
 *
 * @package Funsoul\Funtask
 */
class ProcessGroup extends Base
{
    /** @var string */
    private $workerName = 'Worker';

    /** @var int */
    private $workerNum = 3;

    /** @var JobInterface */
    private $job;

    /**
     * @param string $workerName
     * @return ProcessGroup
     */
    public function setWorkerName(string $workerName): ProcessGroup
    {
        $this->workerName = $workerName;
        return $this;
    }

    /**
     * @param int $workerNum
     * @return ProcessGroup
     */
    public function setWorkerNum(int $workerNum): ProcessGroup
    {
        $this->workerNum = $workerNum;
        return $this;
    }

    /**
     * @param JobInterface $job
     * @return ProcessGroup
     */
    public function setJob(JobInterface $job): ProcessGroup
    {
        $this->job = $job;
        return $this;
    }

    /**
     * ProcessGroup constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->beforeStart();
    }

    public function start()
    {
        if ($this->workerNum <= 0)
            $this->workerNum = 3;

        $masterPid = getmypid();

        for($i = 0; $i < $this->workerNum; $i++) {
            $process = new Process(function(Process $worker) use ($masterPid) {
                $this->workerStart($worker, $masterPid);
            });
            $process->start();
        }

        $this->registerSignal();
    }

    /**
     * @param Process $worker
     * @param int $masterPid
     *
     * @throws \Exception
     */
    private function workerStart(Process $worker, $masterPid = 0)
    {
        $running = true;

        $pid = getmypid();

        $this->setProcessName("{$this->workerName}[{$pid}]");

        while ($running) {

            if ($this->isMasterKilled($masterPid)) {
                $running = false;

                $this->job->handle();
            } else {
                $running = $this->job->handle();
            }
        }

        $worker->exit(0);
    }
}