<?php

/**
 * Default controller
 * @author Ryan Pallas
 * @package PennyAuction
 * @namespace App\Controller
 * @since 2014-10-08
 */

namespace App\Controller;
use Dero\Core\BaseController;
use Dero\Core\TemplateEngine;

class DefaultController extends BaseController
{
    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function index()
    {
        return TemplateEngine::LoadView('header', ['title'=>'Index'])
             . TemplateEngine::LoadView('main')
             . TemplateEngine::LoadView('footer');
    }
}