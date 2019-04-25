<?php declare(strict_types=1);

namespace Funsoul\Funtask;

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
    /** @var int pause second */
    private $workerPauseSec = 0;

    /** @var string worker Name */
    private $workerName = 'Worker';

    /**
     * ProcessGroup constructor.
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
        $this->workerName = $workerName;

        $this->beforeStart();

        $masterPid = getmypid();

        for($i = 0; $i < $workerNum; $i++) {
            $process = new Process(function(Process $worker) use ($masterPid, $job) {
                $this->workerStart($worker, $masterPid, $job);
            });
            $process->start();
        }

        $this->registerSignal();
    }

    /**
     * @param Process $worker
     * @param int $masterPid
     * @param JobInterface $job
     *
     * @throws \Exception
     */
    private function workerStart(Process $worker, $masterPid = 0, JobInterface $job)
    {
        $running = true;

        $pid = getmypid();

        $this->setProcessName("{$this->workerName}[{$pid}]");

        while ($running) {

            if ($this->isMasterKilled($masterPid)) {
                $running = false;

                $job->handle();
            } else {
                $running = $job->handle();

                sleep($this->workerPauseSec);
            }
        }

        $worker->exit(0);
    }
}