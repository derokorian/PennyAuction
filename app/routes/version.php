<?php

/**
 * routes for install pages
 */
$aRoutes[] = [
    'pattern' => '#^install$#i',
    'controller' => 'App\Controller\VersionController',
    'dependencies' => [],
    'method' => 'install',
    'args' => []
];
$aRoutes[] = [
    'pattern' => '#^upgrade$#i',
    'controller' => 'App\Controller\VersionController',
    'dependencies' => [],
    'method' => 'upgrade',
    'args' => []
];
