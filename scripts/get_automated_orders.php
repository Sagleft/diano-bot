<?php
	require_once __DIR__ . '/../vendor/autoload.php';

	$handler = new App\Handler();
	$ordersHandler = new App\Cron\AutomatedOrder();
	
	$orders = $ordersHandler->getOrders();
	print_r($orders);
