<?php

namespace App\Controller;
use Dero\Core\BaseController;
use Dero\Core\TemplateEngine;

/**
 * Error controller
 * @author Ryan Pallas
 * @package SampleSite
 * @namespace App\Controller
 * @since 2014-02-27
 */

class ErrorController extends BaseController
{
    public function error404()
    {
        header('HTTP/1.1 404 Not Found');
        echo TemplateEngine::LoadView('header', ['title'=>'Error']);
        echo '404 Page not found';
        echo TemplateEngine::LoadView('footer');
    }

    public function __call($func, Array $args)
    {
        if( is_numeric($func) &&
            method_exists($this, 'error' . $func) )
        {
            call_user_func([$this, 'error' . $func]);
        }
    }
}