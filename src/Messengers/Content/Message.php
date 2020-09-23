<?php
	namespace App\Messengers\Content;

	class Message {
		public $id = 1;
		public $text = '';
		public $image_url = '';
		
		public function __construct($newtext = '') {
			$this->text = $newtext;
		}
	}
