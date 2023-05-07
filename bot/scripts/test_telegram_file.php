<?php
	require_once __DIR__ . '/../vendor/autoload.php';

	$handler = new App\Handler();
	$messenger_src = new App\Messengers\Telegram();
	$messenger_src->connect();

	$channelid = 'short_story_of_the_week';
	$postID = '318';

	$file_path = $messenger_src->saveTelegramFile($channelid, $postID);
	if($file_path == '') {
		echo 'error: ' . $messenger_src->last_error;
	} else {
		echo 'file saved on ' . $file_path;
	}

	echo PHP_EOL;
