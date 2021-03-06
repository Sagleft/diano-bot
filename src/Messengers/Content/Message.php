<?php
	namespace App\Messengers\Content;

	class Message {
		public $id = 1;
		public $text = '';
		public $type = 'text'; //text, document
		public $image_url = '';

		public $document_path = '';
		public $document_name = '';

		public $messenger_from_tag = 'default';
		public $messenger_from_channel = '';

		//public $have_webpage_preview = false;
		//public $webpage_title = '';
		//public $webpage_descr = '';

		public function __construct($newtext = '') {
			$this->text = $newtext;
		}
	}
