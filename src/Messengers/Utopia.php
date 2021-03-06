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

		public function getOwnContact(): array {
			return $this->client->getOwnContact();
		}

		public function sayHello(): string {
			//return (string) var_dump($this->client);
			if(! $this->isConnected()) {
				return 'not connected';
			} else {
				return json_encode($this->client->getSystemInfo());
			}
		}

		//override MessengerBase\postMessage
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
				if($this->is_debug) {
					echo "use image url: " . $messageObj->image_url . PHP_EOL;
				}
				//post have image
				//TODO: solve this problem by catch data in parser
				try {
					if(\App\Utilities::remoteFileExists($messageObj->image_url)) {
						$image_bytes = file_get_contents($messageObj->image_url);
						$image_b64   = base64_encode($image_bytes);
						$image_name  = 'photo.jpg';
						$result = $this->client->sendChannelPicture(
							$channelid, $image_b64, $image_name
						);
						if($result == '') {
							$this->last_error = 'failed to post picture to ' . $channelid . ' channel';
						}
						sleep(1);
					} else {
						if($this->is_debug) {
							print "remote image " . $messageObj->image_url . " doesn't exists. skip" . PHP_EOL;
						}
					}
				} catch(\Exception $ex) {
					$this->last_error = $ex->getMessage();
				}
			} else {
				if($this->is_debug) {
					echo "message without image." . PHP_EOL;
				}
			}
			if($messageObj->text != '') {
				//if the post contains not only a document, but also a text
				$result = $this->client->sendChannelMessage(
					$channelid, $messageObj->text
				);
				if($result == '') {
					$this->last_error = 'failed to send a message to the channel, received an empty response';
				}
				sleep(1);
			} else {
				if($this->is_debug) {
					echo "message text is not set." . PHP_EOL;
				}
			}
			if($messageObj->type == 'document') {
				//document
				//TODO: will be finalized when there is a method for sending files to the channel

				$message_text = 'Attached file: ' . $messageObj->document_path;
				if($this->is_debug) {
					echo $message_text . PHP_EOL;
				}
				$result = $this->client->sendChannelMessage(
					$channelid, $message_text
				);
				if($result == '') {
					$this->last_error = 'failed to send an attachment to ' . $channelid . ' channel';
				}
				sleep(1);
			}
			if(isset($result) && $result == '') {
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
				return false;
			}

			$search_filter = $channel_info['title'];
			$channel_type  = "5"; //joined
			$query_filter  = new \UtopiaLib\Filter("", "", "1");

			$joined_channels = $this->client->getChannels(
				$search_filter, $channel_type, $query_filter
			);
			if($joined_channels == [] || $joined_channels[0] == [] || $joined_channels[0]['isjoined'] === false) {
				return false;
			}
			return true;
		}

		public function joinChannel($channelid = ''): void {
			$this->client->joinChannel($channelid);
		}

		function removeCachedFile($filename = ''): void {
			$file_path = __DIR__ . '/../../cache/' . $filename;
			unlink($file_path);
		}

		function saveRemoteFile($file_path = ''): string {
			$file_headers = get_headers($file_path, true);
			$file_size = isset($file_headers['Content-Length']) ? (int) $file_headers['Content-Length'] : 0;
			if($file_size > getenv('max_file_size_mb')*1024*1024) {
				$this->last_error = 'the remote file is too large';
				return '';
			}

			$temp_filename = \App\Utilities::generateHEX(10) . '.ext';
			$temp_filepath = __DIR__ . '/../../cache/' . $temp_filename;
			copy($file_path, $temp_filepath);
			if(file_exists($temp_filepath)) {
				return $temp_filepath;
			}
		}

		//override MessengerBase\importMessages
		public function importMessages($channelid = '', $messages_arr = []): bool {
			if(! $this->checkChannelJoined($channelid)) {
				//$this->last_error = 'Failed to check access to the chat or enter it';
				//return false;
				$this->joinChannel($channelid);
			}
			//if(! $this->checkModeratorRights($channelid)) {
			//	$this->last_error = 'No moderator rights to write posts in the channel';
			//	return false;
			//}

			$messages_processed = 0;
			for($i = 0; $i < count($messages_arr); $i++) {

				$message_obj = $messages_arr[$i];
				if(! $this->checkPostISUsed($message_obj)) {
					//post not used
					$status_success = $this->postMessage(
						$channelid, $message_obj
					);
					$this->last_error = $this->client->error;
					sleep(1);
					if(!$status_success) {
						//this is not a bug, this is a feature xD
						$messages_processed--;
					} else {
						$this->markPostUsed($message_obj);
					}
				}
				$messages_processed++;

			}
			return ($messages_processed >= 0);
		}
	}
