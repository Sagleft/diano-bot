<?php
	namespace App\Cron;

	class AutomatedOrder {
		public function getOrders(): array {
			$cache_dir = __DIR__ . '/../../cache/';
			$find_ext = 'json';
			$list = scandir($cache_dir);
			//$order_files = [];
			$orders = [];

			foreach($list as $item) {
				$item_path = $cache_dir . '/' . $item;
				if(! is_dir($item_path)) {
					if(strpos($item, '.' . $find_ext) > 0) {
						//$order_files[] = $item;
						$json = \App\Utilities::fileGetContentsCurl($item_path);
						if(\App\Utilities::isJson($json)) {
							$orders[] = json_decode($json);
						}
					}
				}
			}
			
			return $orders;
		}
	}
