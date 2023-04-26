<?php
	require_once __DIR__ . '/../vendor/autoload.php';

	$handler = new App\Handler();
	
	if(! $handler->executeOrders()) {
		echo 'failed to import messages. last_error: ' . $handler->last_error . PHP_EOL;
	}
