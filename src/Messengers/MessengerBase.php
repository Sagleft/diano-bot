<?php
	namespace App\Messengers;

	class MessengerBase {
		public $tag  = 'default';
		public $name = 'Default messenger';
		public $last_error = '';
		
		protected $db = null; //DataBase object
		
		public function __construct($connection_data = []) {
			$this->connect($connection_data);
		}

		public function connect($connection_data = []): bool {
			//placeholder
			return true;
		}
		
		public function setDB($dbref = null) {
			$this->db = &$dbref;
		}

		public function getChannelContent($channel_ID = ''): array {
			//возвращает массив с новыми постами
			//placeholder
			return [];
		}
		
		public static function getMessengerByTag($tag = 'telegram') {
			switch($tag) {
				default:
					return new MessengerBase();
				case 'telegram':
					return new Telegram();
				case 'utopia':
					return new Utopia();
			}
		}
		
		public function importMessages($channelid = '', $messages_arr = []): bool {
			//placeholder
			return true;
		}
		
		public function markPostUsed($post_id = '0', $channelid = ''): bool {
			$sql_query  = "INSERT INTO channels SET ";
			$sql_query .= "last_post_id='" . $post_id . "',";
			$sql_query .= "channelid='" . $channelid . "',";
			$sql_query .= "messenger='" . $this->tag . "'";

			return $this->db->tryQuery($sql_query);
		}
		
		public function checkPostISUsed($post_id = '0', $channelid = ''): bool {
			$sql_query  = "SELECT id FROM channels where messenger='" . $this->tag . "'";
			$sql_query .= " AND channelid='" . $channelid . "'";
			$sql_query .= " AND last_post_id='" . $post_id . "'";
			$sql_query .= " LIMIT 1";

			return $this->db->checkRowExists($sql_query);
		}
	}
