<?php

namespace App\Controller;
use Dero\Core\BaseController;
use Dero\Core\TemplateEngine;
use Dero\Core\Timing;

/**
 * Error controller
 * @author Ryan Pallas
 * @package PennyAuction
 * @namespace App\Controller
 * @since 2014-10-08
 */

class ErrorController extends BaseController
{
    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function error404()
    {
        header('HTTP/1.1 404 Not Found');

        return TemplateEngine::LoadView('header', ['title'=>'Error'])
             . '<h1 class="error_title">404 Page not found</h1>'
             . TemplateEngine::LoadView('footer');
    }
}