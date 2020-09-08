<?php
	namespace App\Messengers;

	class Utopia extends MessengerBase {
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
			$result = $this->client->sendChannelMessage(
				$channelid, $messageObj->text
			);
			if($result == '') {
				$this->last_error = 'failed to send a message to the channel, received an empty response';
				return false;
			}
			return true;
		}
		
		//override MessengerBase\importMessages
		public function importMessages($channelid = '', $messages_arr = []): bool {
			$messages_imported = 0;
			foreach($messages_arr as $message_obj) {
				$status_success = $this->postMessage(
					$channelid, $message_obj->text
				);
				if($status_success) {
					$messages_imported++;
				}
			}
			return ($messages_imported > 0);
		}
	}
