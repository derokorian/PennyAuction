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
use Dero\Core\Timing;

class DefaultController extends BaseController
{
    public function index()
    {
        echo TemplateEngine::LoadView('header', ['title'=>'Index']);
        echo TemplateEngine::LoadView('main');
        echo TemplateEngine::LoadView('footer');
    }
}