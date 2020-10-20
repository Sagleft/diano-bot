<?php
	namespace App\Messengers\Content;

	class Message {
		public $id = 1;
		public $text = '';
		public $type = 'text'; //text, document
		public $image_url = '';

		public $document_path = '';
		public $document_name = '';

		public function __construct($newtext = '') {
			$this->text = $newtext;
		}
	}
