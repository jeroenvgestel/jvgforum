<?php

    session_start();

    require 'config/config.php';

    $loader = require 'vendor/autoload.php';

    // Extend the composer autoloader to autoload our own libs
    $loader->add('', 'libs/');
    $loader->add('', CONTROLLER_PATH);
    $loader->add('', MODEL_PATH);
    $loader->add('', SERVICE_PATH);
    $loader->add('', REPOSITORY_PATH);

    $user = User::Instance();
    $user->CheckLogin();

    $router = new Router();

    include 'routes.php';

    $router->dispatch(
        $_SERVER['REQUEST_URI'],
        $_SERVER['REQUEST_METHOD']
    );
