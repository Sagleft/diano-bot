<?php
	namespace App\Messengers;

	class Telegram extends MessengerBase {
		public $tag  = 'telegram';
		public $name = 'Telegram';
		
		public function getChannelContent($channel_ID = ''): array {
			if($channel_ID == '') {
				$this->last_error = 'empty channel ID given';
				return [];
			}
		}
		
		public function getChannelPosts($channel_ID = '', $limit = 5): array {
			$api_url  = 'https://tg.i-c-a.su/json/' . $channel_ID;
			$api_url .= '?limit=' . $limit;
			
			$json = \App\Utilities::curlGET($api_url);
			if(! \App\Utilities::isJson($json)) {
				$this->last_error = 'invalid json in response';
				return [];
			}
			
			$result = json_decode($json, true);
			
			if(count($result['messages']) == 0) {
				$this->last_error = '0 messages received';
				return [];
			}

			$messages = [];
			for($i = 0; $i < count($result['messages']); $i++) {
				$post = $result['messages'][$i];
				
				$msg = new Content\Message();
				$msg->id = \App\Utilities::checkINT($post['id']);
				
				$replacement = ""; //\n
				$msg_text = \App\Utilities::dataFilter(
					$post['message'],
					$this->db
				);
				$msg_text = str_replace('\n', "\n", $msg_text);
				$msg_text = str_replace('<br />', $replacement, $msg_text);
				$msg->text = $msg_text;
				
				$post_have_media = isset($post['media']);
				if($post_have_media) {
					switch($post['media']['_']) {
						default:
							//unknown media type
							break;
						case 'messageMediaPhoto':
							//image in post
							$post_type = 'photo';
							$msg->image_url = $this->getPostImageURL($channel_ID, $post['id'], $post_type);
							break;
						case 'messageMediaDocument':
							if(!isset($post['media']['_']['document']) || $post['media']['_']['document']['mime_type'] == 'video/mp4') {
								//video in post
								$post_type = 'video';
								$msg->image_url = $this->getPostImageURL($channel_ID, $post['id'], $post_type);
							} else {
								//another document
								//$post_type = 'document';
								$msg->type = 'document';
								$msg->document_path = $this->getPostMediaUrl($channel_ID, $post['id']);
								//
								$attributes = $post['media']['_']['document']['attributes'];
								$document_name = 'unnamed.ext';
								foreach($attributes as $attribute) {
									if(isset($attribute['file_name'])) {
										$document_name = $attribute['file_name'];
									}
								}
								$msg->document_name = $document_name;
							}
							break;
					}
				}
				$messages[] = $msg;
			}

			return $messages;
		}
		
		function getPostImageURL($channel_ID = '', $postID = 980, $post_type = 'photo'): string {
			$url = 'https://t.me/' . $channel_ID . '/' . $postID . '?embed=1';
			$html = \App\Utilities::curlGET($url);
			$dom = \phpQuery::newDocumentHTML($html);
			
			$regular_function = '/[\s\S]*background-image:[ ]*url\(["\']*([\s\S]*[^"\'])["\']*\)[\s\S]*/u';
			switch($post_type) {
				case 'photo':
					$element_selector = '.tgme_widget_message_photo_wrap';
					break;
				case 'video':
					$element_selector = '.tgme_widget_message_video_thumb';
					break;
			}
			$element_styles = $dom->find($element_selector)->eq(0)->attr('style');
			
			return preg_replace($regular_function, '$1', $element_styles);
		}

		public function saveTelegramFile($channelid = '', $postID = ''): string {
			$file_path = $this->getPostMediaUrl($channelid, $postID);
			$file_headers = get_headers($file_path, true);
			$file_size = isset($file_headers['Content-Length']) ? (int) $file_headers['Content-Length'] : 0;
			if($file_size > getenv('max_file_size_mb')*1024*1024) {
				$this->last_error = 'the remote file is too large';
				return '';
			}

			$temp_filename = \App\Utilities::generateHEX(10) . '.ext';
			$temp_filepath = __DIR__ . '/../../cache/' . $temp_filename;
			copy($file_path, $temp_filepath);
			if(file_exists($temp_filepath)) {
				return $temp_filepath;
			}
		}
		
		function getPostMediaUrl($channelid = '', $postID = ''): string {
			return $file_path = 'https://tg.i-c-a.su/media/' . $channelid . '/' . $postID .'/';
		}
	}
