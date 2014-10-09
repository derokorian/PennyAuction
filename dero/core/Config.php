<?php

namespace Dero\Core;

/**
 * Configuration retriever
 * @author Ryan Pallas
 */
class Config
{
    private static $Config = [];

    /**
     * Loads the configuration if not already initialized
     */
    private static function LoadConfig($file)
    {
        if( !array_key_exists($file, self::$Config) ) {
            $config = [];
            if( is_readable(ROOT . DS . 'dero' . DS . 'config' . DS . $file . '.json') )
            {
                $config = self::MergeConfig(
                    $config,
                    json_decode(
                        strip_json_comments(
                            file_get_contents(ROOT . DS . 'dero' . DS . 'config' . DS . $file . '.json')
                        ),
                        true
                    )
                );
            }
            if( is_readable(ROOT . DS . 'config' . DS . $file . '.json') )
            {
                $config = self::MergeConfig(
                    $config,
                    json_decode(
                        strip_json_comments(
                            file_get_contents(ROOT . DS . 'config' . DS . $file . '.json')
                        ),
                    true
                    )
                );
            }
            self::$Config[$file] = $config;
        }
    }

    /**
     * @param mixed $aConfig
     * @param mixed $aVal
     * @returns array
     */
    private static function MergeConfig(Array $aConfig, Array $aVal)
    {
        $aReturn = [];
        foreach( $aVal as $k => $v )
        {
            if( isset($aConfig[$k]) && is_array($aConfig[$k]) && is_array($v) )
            {
                $aReturn[$k] = self::MergeConfig($aConfig[$k], $v);
            }
            else
            {
                $aReturn[$k] = $v;
            }
        }
        foreach( $aConfig as $k => $v )
        {
            if( !isset($aReturn[$k]) )
            {
                $aReturn[$k] = $v;
            }
        }

        return $aReturn;
    }

    /**
     * Gets a configuration value
     * @param string The name(s) of the configuration parameter to get
     * @example config::GetValue('database','default','engine')
     * @return NULL|string value of the configuration or null if not found
     */
    public static function GetValue()
    {
        if( func_num_args() > 0 ) {
            $args = func_get_args();
            self::LoadConfig($args[0]);
            $last = self::$Config;
            foreach( $args as $arg ) {
                if( isset($last[$arg]) )
                    $last = $last[$arg];
                else
                    return NULL;
            }
            return $last;
        }
        return NULL;
    }
}

function strip_json_comments($strJson) {
    $strJson = preg_replace('@/\*.*?\*/@m', null, $strJson);
    $strJson = preg_replace('@^\s*(//|#).*$@', null, $strJson);
    return $strJson;
}

?>