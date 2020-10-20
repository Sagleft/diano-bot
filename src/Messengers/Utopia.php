<?php
	namespace App\Messengers;

	class Utopia extends MessengerBase {
		public $tag  = 'utopia';
		public $name = 'Utopia';
		protected $client = null;
		
		public function connect($connection_data = []): bool {
			/* if($connection_data == []) {
				$this->last_error = 'unable to connect to ' . $this->name . ' client';
				return false;
			} */
			
			if($connection_data == []) {
				$connection_data = [
					'token' => getenv('utopia_token'),
					'host'  => 'http://' . getenv('utopia_host'),
					'port'  => getenv('utopia_port')
				];
			}
			
			$this->client = new \UtopiaLib\Client(
				$connection_data['token'],
				$connection_data['host'],
				$connection_data['port']
			);
			return $this->isConnected();
		}
		
		public function isConnected(): bool {
			return $this->client->checkClientConnection();
		}
		
		public function sayHello(): string {
			//return (string) var_dump($this->client);
			if(! $this->isConnected()) {
				return 'not connected';
			} else {
				return json_encode($this->client->getSystemInfo());
			}
		}
		
		public function postMessage($channelid = '', $messageObj = null): bool {
			if($channelid == '') {
				$this->last_error = 'channel ID is not set';
				return false;
			}
			if($messageObj == null) {
				$this->last_error = 'empy message obj given';
				return false;
			}
			if($messageObj->image_url != '') {
				//post have image
				$image_bytes = file_get_contents($messageObj->image_url);
				$image_b64   = base64_encode($image_bytes);
				$image_name  = 'photo.jpg';
				$this->client->sendChannelPicture(
					$channelid, $image_b64, $image_name
				);
			}
			$sleep_timeout = 1; //WTF????!
			sleep($sleep_timeout);
			$result = $this->client->sendChannelMessage(
				$channelid, $messageObj->text
			);
			
			if($result == '') {
				$this->last_error = 'failed to send a message to the channel, received an empty response';
				return false;
			}
			return true;
		}

		public function checkModeratorRights($channelid = ''): bool {
			$moderator_pks = $this->client->getChannelModerators($channelid);
			if($moderator_pks == []) {
				return false;
			}
			$pk = $this->client->getMyPubkey();
			return in_array($pk, $moderator_pks);
		}

		public function checkChannelJoined($channelid = ''): bool {
			//TODO: Add the ability to work with private channels. In case the client is not connected to the channel, enter it using the password

			$channel_info = $this->client->getChannelInfo($channelid);
			if($channel_info == []) {
				$this->client->joinChannel($channelid);
				return true;
			}

			$search_filter = $channel_info['title'];
			$channel_type  = "5"; //joined
			$query_filter  = new \UtopiaLib\Filter("", "", "1");

			$joined_channels = $this->client->getChannels(
				$search_filter, $channel_type, $query_filter
			);
			if($joined_channels == [] || $joined_channels[0] == [] || $channel_data['isjoined'] == false) {
				$this->client->joinChannel($channelid);
				return true;
			}
			return false;
		}

		//override MessengerBase\importMessages
		public function importMessages($channelid = '', $messages_arr = []): bool {
			if(! $this->checkChannelJoined($channelid)) {
				$this->last_error = 'Failed to check access to the chat or enter it';
				return false;
			}
			//if(! $this->checkModeratorRights($channelid)) {
			//	$this->last_error = 'No moderator rights to write posts in the channel';
			//	return false;
			//}

			$messages_processed = 0;
			foreach($messages_arr as $message_obj) {

				if(! $this->checkPostISUsed($message_obj->id, $channelid)) {
					//post not used
					$this->markPostUsed($message_obj->id, $channelid);

					$status_success = $this->postMessage(
						$channelid, $message_obj
					);
					sleep(1);
					if(!$status_success) {
						//this is not a bug, this is a feature xD
						$messages_processed--;
					}
				}
				$messages_processed++;

			}
			return ($messages_processed > 0);
		}
	}
