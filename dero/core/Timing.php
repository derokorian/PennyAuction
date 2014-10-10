<?php

namespace Dero\Core;

/**
 * Timing class
 */
class Timing
{
    private static $aTimes = [];
    private function __construct() {}

    public static function start($strTime)
    {
        self::$aTimes[$strTime] = microtime(true) * 1000;
    }

    public static function end($strTime)
    {
        if( isset(self::$aTimes[$strTime]) )
        {
            self::$aTimes[$strTime] = round(microtime(true) * 1000 - self::$aTimes[$strTime], 2);
        }
    }

    public static function getTimings()
    {
        return self::$aTimes;
    }

    public static function setHeaderTimings()
    {
        foreach(self::$aTimes as $strKey => $fTiming)
        {
            header(sprintf('X-%s-Timing: %0.4fms', ucfirst($strKey), $fTiming));
        }
    }

    public static function printTimings()
    {
        foreach(self::$aTimes as $strKey => $fTiming)
        {
            printf("%s timing: %0.4fms\n", $strKey, $fTiming);
        }
    }
}