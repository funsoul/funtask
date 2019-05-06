<?php declare(strict_types=1);

namespace Funsoul\Funtask;
use Funsoul\Funtask\Coroutine\Coroutine;
use Funsoul\Funtask\Coroutine\CoJobInterface;
use Funsoul\Funtask\Process\JobInterface;
use Funsoul\Funtask\Process\ProcessGroup;
use Funsoul\Funtask\Process\ProcessPool;

/**
 * Class Funtask
 *
 * @package Funsoul\Funtask
 */
class Funtask {

    /** @var JobInterface */
    private $job;

    /** @var CoJobInterface */
    private $coJob;

    /** @var string */
    private $type = 'POOL';

    /** @var string */
    private $workerName = 'Worker';

    /** @var int */
    private $workerNum = 3;

    /** @var callable */
    private $finishCallback;

    /**
     * @return JobInterface
     */
    public function getJob(): JobInterface
    {
        return $this->job;
    }

    /**
     * @param JobInterface $job
     * @return Funtask
     */
    public function setJob(JobInterface $job): Funtask
    {
        $this->job = $job;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Funtask
     */
    public function setType(string $type): Funtask
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getWorkerName(): string
    {
        return $this->workerName;
    }

    /**
     * @param string $workerName
     * @return Funtask
     */
    public function setWorkerName(string $workerName): Funtask
    {
        $this->workerName = $workerName;
        return $this;
    }

    /**
     * @return int
     */
    public function getWorkerNum(): int
    {
        return $this->workerNum;
    }

    /**
     * @param int $workerNum
     * @return Funtask
     */
    public function setWorkerNum(int $workerNum): Funtask
    {
        $this->workerNum = $workerNum;
        return $this;
    }

    /**
     * @return CoJobInterface
     */
    public function getCoJob(): CoJobInterface
    {
        return $this->coJob;
    }

    /**
     * @param callable $finishCallback
     * @return Funtask
     */
    public function setFinishCallback(callable $finishCallback): Funtask
    {
        $this->finishCallback = $finishCallback;
        return $this;
    }

    /**
     * @param CoJobInterface $coJob
     * @return Funtask
     */
    public function setCoJob(CoJobInterface $coJob): Funtask
    {
        $this->coJob = $coJob;
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function start()
    {
        $this->checkParams();

        switch (strtoupper($this->type)) {
            case 'POOL':
                $pool = new ProcessPool();
                $pool->setWorkerName($this->workerName)
                    ->setWorkerNum($this->workerNum)
                    ->setJob($this->job)
                    ->start();
                break;
            case 'GROUP':
                $group = new ProcessGroup();
                $group->setWorkerName($this->workerName)
                    ->setWorkerNum($this->workerNum)
                    ->setJob($this->job)
                    ->start();
                break;
            case 'CO':
                $co = new Coroutine();
                $co->setCoNum($this->workerNum)
                    ->setJob($this->coJob)
                    ->start($this->finishCallback);
                break;
            default:
                throw new \Exception('invalid type');
                break;
        }
    }

    private function checkParams()
    {
        if (empty($this->type))
            throw new \InvalidArgumentException('type is empty');
    }
}