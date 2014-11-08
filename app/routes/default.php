<?php

$aRoutes[] = [
    'pattern'       => '#^(home/?)?$#i',
    'controller'    => 'App\Controller\DefaultController',
    'dependencies'  => [],
    'method'        => 'index',
    'args'          => []
];