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
    public function index()
    {
        echo TemplateEngine::LoadView('header', ['title'=>'Index']);
        echo TemplateEngine::LoadView('character/main');
        echo TemplateEngine::LoadView('character/css-images');
        echo TemplateEngine::LoadView('footer');
    }
}