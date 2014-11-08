<?php

/**
 * routes for error pages
 */
$aRoutes['default'] = [
    'pattern'       => '#^error/404$#i',
    'controller'    => 'App\Controller\ErrorController',
    'dependencies'  => [],
    'method'        => 'error404',
    'args'          => []
];

