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
	}
