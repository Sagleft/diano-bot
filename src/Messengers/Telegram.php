<?php
	namespace App\Messengers;

	class Telegram extends MessengerBase {
		public $name = 'Telegram';
		
		public function getChannelContent($channel_ID = ''): array {
			if($channel_ID == '') {
				$this->last_error = 'empty channel ID given';
				return [];
			}
			
			//
		}
		
		public function getChannelPosts($channel_ID = '', $limit = 5): array {
			$api_url  = 'https://tg.i-c-a.su/json/' . $channel_ID;
			$api_url .= '?limit=' . $limit;
			
			$json = \App\Utilities::curlGET($api_url);
			if(! \App\Utilities::isJson($json)) {
				$this->last_error = 'invalid json in response';
				return [];
			}
			
			$result = json_decode($json, true);
			
			if(count($result['messages']) == 0) {
				$this->last_error = '0 messages received';
				return [];
			}
			
			$messages = [];
			for($i = 0; $i < count($result['messages']); $i++) {
				$msg = new Content\Message();
				$msg->id = $result['messages'][$i]['id'];
				$msg->text = str_replace('<br />', "\n", $result['messages'][$i]['message']);
				$messages[] = $msg;
			}
			
			return $messages;
		}
		
		function getPostImage($channel_ID = '', $postID = 980) {
			//TODO
		}
	}
