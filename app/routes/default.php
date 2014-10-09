<?php

$aRoutes[] = [
    'pattern' => '#^(home/?)?$#i',
    'controller' => 'App\Controller\CharacterSheetController',
    'dependencies' => ['App\Model\CharacterModel'],
    'method' => 'index',
    'args' => []
];