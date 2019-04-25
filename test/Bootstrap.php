<?php
require dirname(__DIR__) . '/vendor/autoload.php';

class Consumer implements \Funsoul\Funtask\JobInterface {

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

try {
    // pool of type will restart process

//    $task = new \Funsoul\Funtask\Funtask();
//    $task->setType('POOL')
//        ->setJob(new Consumer())
//        ->setWorkerNum(3)
//        ->setWorkerName('myWorker')
//        ->setPause(1)
//        ->start();


    // group of type will exit process directly

    $task = new \Funsoul\Funtask\Funtask();
    $task->setType('GROUP')
        ->setJob(new Consumer())
        ->setWorkerNum(3)
        ->setWorkerName('myWorker')
        ->setPause(1)
        ->start();

} catch (\Exception $e) {
    echo $e->getMessage();
    exit;
}

