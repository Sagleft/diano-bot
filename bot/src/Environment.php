<?php
	namespace App;
	class Environment {
		public function __construct() {
			if(getenv("DISABLE_ENV_PARSE") != "1") {
				$this->loadFromENV();
			}
		}

		public function loadFromENV() {
			$dotenv = \Dotenv\Dotenv::create(__DIR__ . "/../");
			$dotenv->load();
		}
	}
