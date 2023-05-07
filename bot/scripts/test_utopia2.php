<?php
	require_once __DIR__ . '/../vendor/autoload.php';
	
	$handler = new App\Handler();
	$messenger = new App\Messengers\Utopia();
	$messenger->connect();

	$channelID = '71FAD8E273A72241F8D5B725742B53B0';
	$message_obj = new App\Messengers\Content\Message('test message');

	$status_success = $messenger->postMessage($channelID, $message_obj);
	echo (string) $status_success;
