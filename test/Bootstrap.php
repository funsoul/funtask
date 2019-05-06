<?php
require dirname(__DIR__) . '/vendor/autoload.php';

class Consumer implements \Funsoul\Funtask\Process\JobInterface {

    /**
     * business job
     *
     * @return bool [exit process or not]
     */
    public function handle(): bool
    {
        $pid = getmypid();

        $i = 0;
        $running = true;
        while ($running) {
            echo "{$pid}: " . $i++ . PHP_EOL;

            if ($i == 5)
                $running = false;
        }

        // exit current process
        return false;
    }
}

class ConsumerCo implements \Funsoul\Funtask\Coroutine\CoJobInterface {

    /**
     * @param \Swoole\Http\Request $request
     * @return mixed|void
     */
    public function handle(\Swoole\Http\Request $request)
    {
        $cid = Co::getuid();

        $i = 0;
        $running = true;
        while ($running) {
            echo "{$cid}: " . $i++ . PHP_EOL;

            Co::sleep(1);

            if ($i == 5)
                $running = false;
        }
    }
}

try {
    ## pool of type will restart process

//    $task = new \Funsoul\Funtask\Funtask();
//    $task->setType('POOL')
//        ->setJob(new Consumer())
//        ->setWorkerNum(3)
//        ->setWorkerName('myWorker')
//        ->start();


    ## group of type will exit process directly

//    $task = new \Funsoul\Funtask\Funtask();
//    $task->setType('GROUP')
//        ->setJob(new Consumer())
//        ->setWorkerNum(3)
//        ->setWorkerName('myWorker')
//        ->start();

    ## coroutine of type

//    $task = new \Funsoul\Funtask\Funtask();
//    $task->setType('CO')->setCoJob(new ConsumerCo())->setWorkerNum(3);
//    /** var \Swoole\Http\Response $response */
//    $task->setFinishCallback(function ($response) {
//        $response->end('finished');
//    });
//    $task->start();

    ## coroutine

    $co = new \Funsoul\Funtask\Coroutine\Coroutine();
    $co->setHost('127.0.0.1')
        ->setPort(9501)
        ->setWorkerNum(1)
        ->setCoNum(3)
        ->setJob(new ConsumerCo());

    /** var \Swoole\Http\Response $response */
    $co->start(function ($response) {
        $response->end("finished!\n");
    });

} catch (\Exception $e) {
    echo $e->getMessage();
    exit;
}

