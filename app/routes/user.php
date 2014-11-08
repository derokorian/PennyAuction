<?php

/**
 * routes for user pages
 */
$aRoutes[] = [
    'pattern'       => '#^user/login/?#i',
    'controller'    => 'App\Controller\UserController',
    'dependencies'  => ['App\Model\UserModel'],
    'method'        => 'login',
    'args'          => []
];
$aRoutes[] = [
    'pattern'       => '#^user/logout/?#i',
    'controller'    => 'App\Controller\UserController',
    'dependencies'  => ['App\Model\UserModel'],
    'method'        => 'logout',
    'args'          => []
];
$aRoutes[] = [
    'pattern'       => '#^user/saveUser/?#i',
    'controller'    => 'App\Controller\UserController',
    'dependencies'  => ['App\Model\UserModel'],
    'method'        => 'saveUser',
    'args'          => []
];
$aRoutes[] = [
    'pattern'       => '#^user/getUsers/?#i',
    'controller'    => 'App\Controller\UserController',
    'dependencies'  => ['App\Model\UserModel'],
    'method'        => 'getUsers',
    'args'          => []
];
$aRoutes[] = [
    'pattern'       => '#^user/current/?#i',
    'controller'    => 'App\Controller\UserController',
    'dependencies'  => ['App\Model\UserModel'],
    'method'        => 'getCurrentUser',
    'args'          => []
];
