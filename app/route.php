<?php

/**
 * array of routes
 * 
 * format route example: [
 *    '/home' => [
 *        'GET' => [ControllerClass::class, 'method']
 *    ]
 * ]
 */

use App\Controller\ArticleController;

return [
    '/article/create' => [
        'POST' => [ArticleController::class, 'create']
    ],
    '/article/update' => [
        'PUT' => [ArticleController::class, 'update']
    ]
];
