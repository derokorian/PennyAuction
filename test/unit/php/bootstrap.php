<?php

define('ROOT', dirname(dirname(dirname(__DIR__))));
define('DS', DIRECTORY_SEPARATOR);

spl_autoload_register(function ($strClass)
{
    $strFile = $strClass . '.php';
    $strNameSpace = '';
    if ( ($iLast = strripos($strClass, '\\')) !== FALSE ) {
        $strNameSpace = DS . str_replace('\\',DS,substr($strClass, 0, $iLast));
        $strNameSpace = implode('_', preg_split('/(?<=[a-zA-Z])(?=[A-Z])/s', $strNameSpace));
        $strFile = substr($strClass, $iLast + 1) . '.php';
    }
    $strFilePath = ROOT . strtolower($strNameSpace) . DS . $strFile;
    if( is_readable($strFilePath) ) {
        require_once $strFilePath;
        return TRUE;
    }
    return FALSE;
});

$files = glob(ROOT . '/dero/settings/*.php');
foreach($files as $file)
{
    if( is_readable($file) && is_file($file) )
        require_once $file;
}