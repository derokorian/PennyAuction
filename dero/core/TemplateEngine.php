<?php

/**
 * Created by PhpStorm.
 * User: Ryan
 * Date: 12/20/13
 * Time: 7:33 PM
 */

namespace Dero\Core;


class TemplateEngine {
    private static $NAMESPACES = ['Dero\Core\\', 'App\Controller\\'];

    public static function LoadView($strView, Array $vars = [])
    {
        $aExt = ['phtml','php', 'tpl', 'html'];
        foreach( $aExt as $strExt )
        {
            $strFile = ROOT . '/app/view/' .  $strView . '.' . $strExt;
            if( is_readable($strFile) )
            {
                $strContent = file_get_contents($strFile);
                return self::ParseTemplate($strContent, $vars);
            }
        }

    }

    public static function ParseTemplate($strContent, Array $vars = [])
    {
        extract($vars);

        // replace iterations
        if (preg_match_all('#(?<!\{)\{each\|(\w+)>(\w+)\}(.*?)\{\/each\}#is', $strContent, $matches)) {
            foreach ($matches[1] as $k => $match) {
                $body = '';
                if( isset($$match) && is_array($$match) ) {
                    $RepeatBody = $matches[3][$k];
                    $replaces = $$match;
                    foreach( $replaces as $replace ) {
                        $body .= str_replace('{'.$matches[2][$k].'}', $replace, $RepeatBody);
                    }
                }
                $strContent = str_replace($matches[0][$k], $body, $strContent);
            }
        }

        // call static methods with arguments
        if( preg_match_all('#(?<!\{)\{(\w+)>(\w+)\((.*)\)\}#i', $strContent, $matches) ) {
            foreach ($matches[0] as $k => $match) {
                $class = $matches[1][$k];
                $method = $matches[2][$k];
                $args = $matches[3][$k];
                foreach( self::$NAMESPACES as $ns ) {
                    if( class_exists($ns . $class) ) {
                        $class = $ns . $class;
                    }
                }
                if( class_exists($class) || $class == 'static' ) {
                    if( method_exists($class, $method) || ($class == 'static' && function_exists($method)) ) {
                        if( preg_match('/(?<![^\\\]\\\)' . preg_quote(',', '/') . '/' ,$args) ) {
                            $args = preg_split('/(?<![^\\\]\\\)' . preg_quote(',', '/') . '/', $args);;
                            foreach( $args as &$arg )
                            {
                                if( isset($$arg) )
                                    $arg = $$arg;
                                unset($arg);
                            }
                            $strReplace = call_user_func_array($class .'::'. $method, $args);
                        } elseif( strlen($args) > 0 ) {
                            if( isset($$args) )
                            {
                                $strReplace = call_user_func($class .'::'. $method, $$args);
                            }
                            else
                            {
                                $strReplace = call_user_func($class .'::'. $method, $args);
                            }
                        } else {
                            $strReplace = call_user_func($class .'::'. $method);
                        }
                        $strContent = str_replace($match, $strReplace, $strContent);
                    } else {
                        throw new \UnexpectedValueException('Method not found ('.$class.'::'.$method.')');
                    }
                } else {
                    throw new \UnexpectedValueException('Class not found ('.$class.')');
                }
                $strContent = str_replace($match, '', $strContent);
            }
        }

        // replace embedded templates, variables, and constants
        if (preg_match_all('#(?<!\{)\{(\w+)(\|([\w\\\/]+))?\}#i', $strContent, $matches)) {
            foreach ($matches[1] as $k => $match) {
                switch ($match) {
                    case 'tpl':
                        $strContent = str_replace($matches[0][$k],
                            self::LoadView($matches[3][$k]),
                            $strContent);
                        break;
                    case '_SERVER':
                        $strContent = str_replace($matches[0][$k],
                            isset($_SERVER[$matches[3][$k]]) ? $_SERVER[$matches[3][$k]] : '',
                            $strContent);
                        break;
                    case '_POST':
                        $strContent = str_replace($matches[0][$k],
                            isset($_POST[$matches[3][$k]]) ? $_POST[$matches[3][$k]] : '',
                            $strContent);
                        break;
                    case '_GET':
                        $strContent = str_replace($matches[0][$k],
                            isset($_GET[$matches[3][$k]]) ? $_GET[$matches[3][$k]] : '',
                            $strContent);
                        break;
                    case defined($match):
                        $strContent = str_replace($matches[0][$k],
                            constant($match),
                            $strContent);
                        break;
                    case isset($$match):
                        $strContent = str_replace($matches[0][$k],
                            $$match,
                            $strContent);
                        break;
                    default:
                        foreach( self::$NAMESPACES as $ns ) {
                            $class = $ns . $match;
                            if (class_exists($class)) {
                                $action = $matches[3][$k];
                                $class = new $class();
                                if (method_exists($class, $action)) {
                                    $strContent = str_replace($matches[0][$k], call_user_func_array('self::View', $class->$action()), $strContent);
                                } elseif (property_exists($class, $action)) {
                                    $strContent = str_replace($matches[0][$k], $class->$action, $strContent);
                                }
                            }
                        }
                }
            }
        }

        if (preg_match_all('#(?<!\{)\{(\w+)\|(.+)\}#i', $strContent, $matches)) {
            foreach($matches[1] as $k => $match)
            {
                if( isset($$match) )
                {
                    $replace = $$match;
                }
                else
                {
                    $replace = $matches[2][$k];
                }
                $strContent = str_replace($matches[0][$k], $replace, $strContent);
            }
        }

        return $strContent;
    }

    public static function __callStatic($func, $args)
    {
        if( function_exists($func) )
        {
            return call_user_func_array($func, $args);
        }
        return false;
    }
} 