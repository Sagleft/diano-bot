<?php
	namespace App\Messengers;

	class MessengerBase {
		public $name = 'Default messenger';
		public $last_error = '';
		
		public function __construct($connection_data = []) {
			$this->connect($connection_data);
		}

		public function connect($connection_data = []): bool {
			//placeholder
			return true;
		}

		public function getChannelContent($channel_ID = ''): array {
			//возвращает массив с новыми постами
			//placeholder
			return [];
		}
		
	}
