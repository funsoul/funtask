<?php

namespace Funsoul\Funtask\Task;

interface Base {
    public static function getData();
    public static function run($params);
}