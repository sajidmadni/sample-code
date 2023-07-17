<?php

namespace Navio\HospitalBundle\Service;

/**
 * Various debugging utilities fro practice unite use.
 */
class Debug{
    /**
     * call doctrine dump on each item in passed array. 
     * @param array $array
     */
    public static function dump($array,$stack=true,$exit=true){
        echo "<PRE>\n";
        if(is_array($array)){
            foreach($array as $item){
                \Doctrine\Common\Util\Debug::dump($item);            
            }
        }
        else{
            \Doctrine\Common\Util\Debug::dump($array);  //one item
        }
        if($stack){
            debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        }
        if($exit){
            exit;
        }
    }
    
    public static function logCaller($text,$level=LOG_INFO,$stack=2){
        for($i=$stack;$i>=0;$i--){
            $t2 = "logCaller ".@debug_backtrace()[$i]['file'].":".@debug_backtrace()[$i]['line']."/".@debug_backtrace()[$i]['function'];
            self::log($t2,$level);
        }
        self::log($text,$level);
    }

        
    public static function log($text,$level=LOG_INFO){
        global $kernel; //TODO  Can I get this off the container?
        if($kernel){
            $kernel->getContainer()->get('logger')->info($text);
        }
        syslog($level,$text);        
    }
}
