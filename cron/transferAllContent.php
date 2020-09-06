<?php
	require_once __DIR__ . '/../vendor/autoload.php';
	
	$handler = new App\Handler();
	
	$messenger_source      = new App\Messengers\Telegram();
	$messenger_destination = new App\Messengers\Utopia();
	
	$messenger_source->connect();
	$messenger_destination->connect();
	
	$handler->logic->transferAllContent();
