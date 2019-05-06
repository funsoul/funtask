<?php declare(strict_types=1);

namespace Funsoul\Funtask\Coroutine;

/**
 * Class WaitGroup
 *
 * @package Funsoul\Funtask\Coroutine
 */
class WaitGroup
{
    private $count = 0;
    private $chan;

    public function __construct()
    {
        $this->chan = new \Chan;
    }

    public function add()
    {
        $this->count++;
    }

    public function done()
    {
        $this->chan->push(true);
    }

    public function wait()
    {
        for($i = 0; $i < $this->count; $i++) {
            $this->chan->pop();
        }
    }
}