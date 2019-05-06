<?php declare(strict_types=1);

namespace Funsoul\Funtask\Coroutine;

/**
 * Class Coroutine
 *
 * @package Funsoul\Funtask\Coroutine
 */
class Coroutine
{
    /** @var string */
    private $host = "127.0.0.1";

    /** @var int */
    private $port = 9501;

    /** @var int */
    private $workerNum = 1;

    /** @var int */
    private $coNum = 3;

    /** @var CoJobInterface */
    private $job;

    /**
     * @param int $coNum
     * @return Coroutine
     */
    public function setCoNum(int $coNum): Coroutine
    {
        $this->coNum = $coNum;
        return $this;
    }

    /**
     * @param CoJobInterface $job
     * @return Coroutine
     */
    public function setJob(CoJobInterface $job): Coroutine
    {
        $this->job = $job;
        return $this;
    }

    /**
     * @param string $host
     * @return Coroutine
     */
    public function setHost(string $host): Coroutine
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @param int $port
     * @return Coroutine
     */
    public function setPort(int $port): Coroutine
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @param int $workerNum
     * @return Coroutine
     */
    public function setWorkerNum(int $workerNum): Coroutine
    {
        $this->workerNum = $workerNum;
        return $this;
    }

    private function beforeStart()
    {
        if (empty($this->host))
            $this->host = "127.0.0.1";

        if (empty($this->port) || ! is_numeric($this->port))
            $this->port = 9501;

        if (empty($this->workerNum) || ! is_numeric($this->workerNum))
            $this->workerNum = 1;

        if (empty($this->coNum) || $this->coNum <= 0)
            $this->coNum = 3;
    }

    public function start(callable $finishCallback)
    {
        $this->beforeStart();

        $server = new \Swoole\Http\Server($this->host, $this->port, \SWOOLE_BASE);

        $server->set([
            'worker_num' => $this->workerNum
        ]);

        $server->on('Request', function ($request, $response) use ($server, $finishCallback) {
            $wg = new WaitGroup();
            for ($i = 0; $i < $this->coNum; $i++) {
                $wg->add();
                go(function() use ($wg, $request) {
                    $this->job->handle($request);
                    $wg->done();
                });
            }
            $wg->wait();

            if (is_callable($finishCallback)) {
                $finishCallback($response);
            } else {
                throw new \Exception('finishCallback is not callable');
            }
        });

        $server->start();
    }
}