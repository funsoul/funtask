<?php

namespace Funsoul\Funtask\Task;

interface Base {
    public static function dispatch();
    public static function consume($params);
}