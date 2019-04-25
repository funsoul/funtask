<?php
namespace Funsoul\Funtask;

/**
 * Interface JobInterface
 * @package Funsoul\Funtask
 */
interface JobInterface
{
    /**
     * @return bool [running or not]
     */
    public function handle(): bool;
}