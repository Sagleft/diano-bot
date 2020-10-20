<?php
	require_once __DIR__ . '/../vendor/autoload.php';

	$handler = new App\Handler();

	$messenger_src = new App\Messengers\Telegram();
	$messenger_src->connect();

	$tg_channel_ID  = 'Iifehacker';
	$utp_channel_ID = '80593384E4B2FE2C426A830F50C190B7';
	$load_messages_count = 1;
	$messages = $messenger_src->getChannelPosts($tg_channel_ID, $load_messages_count);

	if($messages == []) {
		echo '0 messages imported';
		exit;
	}

	$messenger_dest = new App\Messengers\Utopia();
	$messenger_dest->connect();
	
	echo 'Total imported ' . count($messages) . ' messages.' . PHP_EOL;
	
	for($i = 0; $i < count($messages); $i++) {
		$status_success = $messenger_dest->postMessage(
			$utp_channel_ID, $messages[$i]
		);
		if(!$status_success) {
			echo $messenger_dest->last_error . PHP_EOL;
		} else {
			echo 'message #' . $messages[$i]->id . ' imported' . PHP_EOL;
		}
	}
