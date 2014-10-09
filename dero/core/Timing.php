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
            return self::$aTimes[$strTime] . "ms";
        }
    }
}