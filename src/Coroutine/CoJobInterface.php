<?php
namespace Funsoul\Funtask\Coroutine;

/**
 * Interface CoJobInterface
 * @package Funsoul\Funtask\Coroutine
 */
interface CoJobInterface
{
    /**
     * @param \Swoole\Http\Request $request
     * @return mixed
     */
    public function handle(\Swoole\Http\Request $request);
}