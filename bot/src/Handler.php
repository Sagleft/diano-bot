<?php
	namespace App;

	class Handler {
		public $logic = null;
		public $user  = null;
		public $renderT = null;
		public $last_error = '';

		protected $db      = null;
		protected $enviro  = null;
		protected $db_enabled = false;

		public function __construct() {
			$this->enviro     = new Environment();
			$this->db_enabled = getenv('db_enabled') == '1';
			if($this->isDBEnabled()) {
				$this->db = new DataBase();
			}
		}

		function isDBEnabled(): bool {
			return $this->db_enabled;
		}

		public function dataFilter($str = ''): string {
			if($this->isDBEnabled()) {
				return Utilities::dataFilter($str, $this->db);
			} else {
				return Utilities::dataFilter($str);
			}
		}
		
		public function checkINT($value = 0): int {
			return Utilities::dataFilter($value, $this->db);
		}
		
		public function executeOrders(): bool {
			$status_success = false;
			$ordersHandler = new Cron\AutomatedOrder();
	
			$orders = $ordersHandler->getOrders();
			foreach($orders as $order) {
				$messenger_source = Messengers\MessengerBase::getMessengerByTag($order->source->tag);
				$messenger_dest   = Messengers\MessengerBase::getMessengerByTag($order->destination->tag);

				$messenger_source->setDB($this->db);
				$messenger_dest->setDB($this->db);
				if(! $messenger_source->connect()) {
					$this->last_error = 'Failed to connect to ' . $messenger_source->name . ' client';
					return false;
				}
				if(! $messenger_dest->connect()) {
					$this->last_error = 'Failed to connect to ' . $messenger_dest->name . ' client';
					return false;
				}

				$messages = $messenger_source->getChannelPosts($order->source->channelid, $order->params->limit);
				$status_success = $messenger_dest->importMessages(
					$order->destination->channelid,
					$messages
				);
				if(!$status_success) {
					$this->last_error = $messenger_dest->last_error;
				}
			}
			return $status_success;
		}
		
	}
