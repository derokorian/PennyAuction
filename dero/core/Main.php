<?php
/**
 * Main
 * @author Ryan Pallas
 * @package DeroFramework
 * @namespace dero\core
 * @since 2013-12-06
 */

namespace Dero\Core;

class Main
{
    public static function init()
    {
        define('APP_ROOT', ROOT . DS . 'app' . DS);
        /*
         * Define error reporting settings
         */
        define('IS_DEBUG', (bool)getenv('PHP_DEBUG')  === true);
        if( IS_DEBUG )
        {
            ini_set('error_reporting', E_ALL);
            ini_set('display_errors', true);
            ini_set('log_errors', false);
        }
        else
        {
            ini_set('error_reporting', E_WARNING);
            ini_set('display_errors', false);
            ini_set('log_errors', true);
            ini_set('error_log', dirname(__DIR__) . '/logs/' . date('Y-m-d') . '-error.log');
        }

        if( isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST' &&
            isset($_SERVER["CONTENT_TYPE"]) && stripos($_SERVER["CONTENT_TYPE"], "application/json") === 0)
        {
            $_POST = json_decode(file_get_contents("php://input"), true);
        }

        // Load settings
        $files = glob(dirname(__DIR__) . '/settings/*.php');
        foreach($files as $file)
        {
            if( is_readable($file) && is_file($file) )
                require_once $file;
        }

        $strSessionName = Config::GetValue('website','session_name');
        session_name($strSessionName);
        session_start();

        self::LoadRoute();
    }

    private static function LoadRoute()
    {
        $bRouteFound = false;
        if( PHP_SAPI === 'cli' )
        {
            $strURI = !empty($GLOBALS["argv"][1]) ? $GLOBALS["argv"][1] : '';
            define('IS_API_REQUEST', false);
        }
        else
        {
            $strURI = trim($_GET['REQUEST'], '/');
            if( substr($strURI, 0, 3) == 'api' )
            {
                define('IS_API_REQUEST', true);
                $strURI = substr($strURI, 4);
            }
            else
            {
                define('IS_API_REQUEST', false);
            }
        }

        // Load defined routes
        $aRoutes = [];
        $files = glob(ROOT . '/app/routes/*.php');
        foreach($files as $file)
        {
            include_once $file;
        }
        $files = glob(ROOT . '/dero/routes/*.php');
        foreach($files as $file)
        {
            include_once $file;
        }

        $fLoadRoute = function(Array $aRoute)
        {
            if( empty($aRoute['dependencies']) )
                $oController = new $aRoute['controller']();
            else
            {
                $aDep = array();
                foreach( $aRoute['dependencies'] as $strDependency )
                {
                    if( class_exists($strDependency) )
                    {
                        $aDep[] = new $strDependency();
                    }
                }
                $oRef = new \ReflectionClass($aRoute['controller']);
                $oController = $oRef->newInstanceArgs($aDep);
            }

            if( is_numeric($aRoute['method']) )
            {
                $method = $aRoute['Match'][$aRoute['method']];
            }
            else
            {
                $method = $aRoute['method'];
            }

            if( empty($aRoute['args']) || !isset($aRoute['Match'][$aRoute['args'][0]]))
            {
                $oController->{$method}();
            }
            else
            {
                Timing::start('controller');
                if( count($aRoute['args']) > 1 )
                {
                    $args = [];
                    foreach($aRoute['args'] as $arg)
                    {
                        if( isset($aRoute['Match'][$arg]) )
                        {
                            $args[] = $aRoute['Match'][$arg];
                        }
                    }
                    $mRet = call_user_func_array([$oController, $method], $args);
                }
                else
                {
                    $mRet = $oController->{$method}($aRoute['Match'][$aRoute['args'][0]]);
                }

                if( is_scalar($mRet) )
                {
                    echo $mRet;
                }
                elseif( !empty($mRet) )
                {
                    echo json_encode($mRet);
                }
                Timing::end('controller');
            }
        };

        // Attempt to find the requested route
        foreach($aRoutes as $aRoute)
        {
            if( preg_match($aRoute['pattern'], $strURI, $match) )
            {
                $bRouteFound = true;
                $aRoute['Request'] = $strURI;
                $aRoute['Match'] = $match;
                $fLoadRoute($aRoute);
                break;
            }
        }

        // If route wasn't found, try to load default
        if( !$bRouteFound && isset($aRoutes['default']) )
        {
            $fLoadRoute($aRoutes['default']);
        }
        else
        {
            // ToDo: Need some handling here for undefined request and no default
        }
    }
} 