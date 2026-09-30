<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\View\ViewServiceProvider;

define('LARAVEL_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';

/** @var Application $app */
$app = require __DIR__ . '/../bootstrap/app.php';
$app->register(ViewServiceProvider::class);
$app->handleRequest(Request::capture());