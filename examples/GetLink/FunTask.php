<?php
namespace App\Task;

use Funsoul\Funtask\Task\Base as Task;

class FunTask implements Task
{
    private static $urls = [
        'http://www.baidu.com',
        'http://qq.com',
        'http://sina.com',
        'https://www.v2ex.com',
        'https://www.csdn.net'
    ];
    public static function dispatch()
    {
        return array_pop(static::$urls);
    }

    public static function consume($url)
    {
        $res[$url] = static::getPageLink($url);
        $count[$url] = count($res[$url]);
        var_dump($count);
    }

    private static function getPageLink($url){
        set_time_limit(0);
        $html=file_get_contents($url);
        preg_match_all("/<a(s*[^>]+s*)href=([\"|']?)([^\"'>\s]+)([\"|']?)/ies",$html,$out);
        $arrLink=$out[3];
        $arrUrl=parse_url($url);
        $dir='';
        if(isset($arrUrl['path'])&&!empty($arrUrl['path'])){
            $dir=str_replace("\\","/",$dir=dirname($arrUrl['path']));
            if($dir=="/"){
                $dir="";
            }
        }
        if(is_array($arrLink)&&count($arrLink)>0){
            $arrLink=array_unique($arrLink);
            foreach($arrLink as $key=>$val){
                $val=strtolower($val);
                if(preg_match('/^#*$/isU',$val)){
                    unset($arrLink[$key]);
                }elseif(preg_match('/^\//isU',$val)){
                    $arrLink[$key]='http://'.$arrUrl['host'].$val;
                }elseif(preg_match('/^javascript/isU',$val)){
                    unset($arrLink[$key]);
                }elseif(preg_match('/^mailto:/isU',$val)){
                    unset($arrLink[$key]);
                }elseif(!preg_match('/^\//isU',$val)&&strpos($val,'http://')===FALSE){
                    $arrLink[$key]='http://'.$arrUrl['host'].$dir.'/'.$val;
                }
            }
        }
        sort($arrLink);
        return $arrLink;
    }
        
}