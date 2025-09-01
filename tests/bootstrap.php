<?php
    
    session_start();
    
    require 'config/config.php';
    
    $loader = require 'vendor/autoload.php';
    
    // Extend the composer auto loader to autoload our own libs
    $loader->add('', 'libs/');
    $loader->add('', CONTROLLER_PATH);
    $loader->add('', MODEL_PATH);
    $loader->add('', SERVICE_PATH);
    $loader->add('', STRUCTURES_PATH);
    
    $user = User::Instance();
    $user->CheckLogin();