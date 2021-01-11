<?php
    require_once __DIR__ . '/../vendor/autoload.php';

    $handler = new App\Handler();
    $messenger = new App\Messengers\Utopia();
    $messenger->connect();
    echo json_encode($messenger->getOwnContact());
