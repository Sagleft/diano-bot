<?php
	require_once __DIR__ . '/../vendor/autoload.php';

	$handler = new App\Handler();

	$messenger = new App\Messengers\Telegram();
	$messenger->connect();

	$channel_ID = 'sagleft_log';
	$messages = $messenger->getChannelPosts($channel_ID, 2);

	if($messages == []) {
		echo 'Последняя ошибка: ' . $messenger->last_error . PHP_EOL;
	} else {
		echo 'Получено ' . count($messages) . ' сообщений' . PHP_EOL;
		echo 'Последнее сообщение с ID ' . (string) $messages[0]->id . ': ' . PHP_EOL . $messages[0]->text . PHP_EOL;
	}
