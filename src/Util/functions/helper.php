<?php

/**
 * memory  convert
 * @param $size
 * @return string
 */
function memConvert($size){
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}