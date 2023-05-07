<?php
	namespace App\Messengers;

	class MessengerBase {
		public $tag  = 'default';
		public $name = 'Default messenger';
		public $last_error = '';

		protected $db = null; //DataBase object
		protected $is_debug = false;

		public function __construct($connection_data = []) {
			$this->connect($connection_data);
			$this->is_debug = getenv('is_debug') == '1';
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

		public function postMessage($channelid = '', $messageObj = null): bool {
			//placeholder
			return true;
		}

		public function importMessages($channelid = '', $messages_arr = []): bool {
			//placeholder
			return true;
		}

		public function markPostUsed($msg_obj): bool {
			if($this->is_debug) {
				echo "mark post " . $msg_obj->id . " as used\n";
			}

			$sql_query  = "INSERT INTO channels SET ";
			$sql_query .= "last_post_id='" . $msg_obj->id . "',";
			$sql_query .= "channelid='" . $msg_obj->messenger_from_channel . "',";
			$sql_query .= "messenger='" . $msg_obj->messenger_from_tag . "'";

			return $this->db->tryQuery($sql_query);
		}

		public function checkPostISUsed($msg_obj): bool {
			$sql_query  = "SELECT id FROM channels where messenger='" . $msg_obj->messenger_from_tag . "'";
			$sql_query .= " AND channelid='" . $msg_obj->messenger_from_channel . "'";
			$sql_query .= " AND last_post_id='" . $msg_obj->id . "'";
			$sql_query .= " LIMIT 1";

			$isUsed = $this->db->checkRowExists($sql_query);
			if($this->is_debug) {
				echo "post " . $msg_obj->id . " already been used\n";
			}

			return $isUsed;
		}

		public function isPostContainsAds($post_text = ''): bool {
			$adFiltersFilePath = __DIR__ . "/../../ad-filters/" . $this->tag . ".json";
			if(!file_exists($adFiltersFilePath)) {
				//filters file not found
				return false;
			}
			$json = file_get_contents($adFiltersFilePath);
			if(! \App\Utilities::isJson($json)) {
				//failed to parse ad filters json
				return false;
			}
			$hashtags = json_decode($json, true);
			for($i = 0; $i < count($hashtags); $i++) {
				$adHashTagPos = strripos($post_text, $hashtags[$i]);
				if($adHashTagPos !== false) {
					if($this->is_debug) {
						echo "post contains advertising. skip it\n";
					}

					return true;
				}
			}
			return false;
		}
	}
