<?php declare(strict_types=1);

namespace Funsoul\Funtask;

/**
 * Class Funtask
 *
 * @package Funsoul\Funtask
 */
class Funtask {

    /** @var JobInterface */
    private $job;

    /** @var string */
    private $type = 'POOL';

    /** @var string */
    private $workerName = 'Worker';

    /** @var int */
    private $workerNum = 3;

    /** @var int */
    private $pause = 0;

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
     * @return int
     */
    public function getPause(): int
    {
        return $this->pause;
    }

    /**
     * @param int $pause
     * @return Funtask
     */
    public function setPause(int $pause): Funtask
    {
        $this->pause = $pause;
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
                $pool = new ProcessPool($this->job);
                $pool->setWorkerName($this->workerName)
                    ->setWorkerPauseSec($this->pause)
                    ->start();
                break;
            case 'GROUP':
                new ProcessGroup($this->job, $this->workerNum, $this->workerName, $this->pause);
                break;
            default:
                throw new \Exception('invalid type');
                break;
        }
    }

    private function checkParams()
    {
        if (! is_object($this->job))
            throw new \InvalidArgumentException('Job is empty');

        if (empty($this->type))
            throw new \InvalidArgumentException('type is empty');
    }
}