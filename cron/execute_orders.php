<?php
	require_once __DIR__ . '/../vendor/autoload.php';

	$handler = new App\Handler();
	$ordersHandler = new App\Cron\AutomatedOrder();
	
	$orders = $ordersHandler->getOrders();
	foreach($orders as $order) {
		$messenger_source = App\Messengers\MessengerBase::getMessengerByTag($order->source->tag);
		$messenger_dest   = App\Messengers\MessengerBase::getMessengerByTag($order->destination->tag);

		$messenger_source->connect();
		$messenger_dest->connect();

		$messages = $messenger_source->getChannelPosts($order->source->channelid, $order->params->limit);
		$status_success = $messenger_dest->importMessages(
			$order->destination->channelid,
			$messages
		);
		if(! $status_success) {
			echo 'failed to import messages. last_error: ' . $messenger_dest->last_error;
		}
	}
