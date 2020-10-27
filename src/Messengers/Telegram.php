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

		function parsePostType($raw_post_data = [], $channel_ID = ''): array {
			$post_data = [
				'type'          => 'text',
				'text'          => $raw_post_data['message'],
				'document_path' => '',
				'document_name' => '',
				'image_url'     => '',
				//'webpage_title' => '',
				//'webpage_descr' => '',
				//'have_webpage_preview' => false
			];

			if(!isset($raw_post_data['media'])) {
				return $post_data;
			}
			switch($raw_post_data['media']['_']) {
				default:
					//unknown media type
					$post_data['type'] = 'text';
					break;
				case 'messageMediaPhoto':
					//image in post
					$post_data['type'] = 'photo';
					$post_data['image_url'] = $this->getPostImageURL($channel_ID, $raw_post_data['id'], $post_data['type']);
					break;
				case 'messageMediaWebPage':
					//will display as an image
					$post_data['type'] = 'photo';
					$post_data['image_url'] = $this->getPostImageURL($channel_ID, $raw_post_data['id'], $post_data['type']);
					if(isset($raw_post_data['media']['webpage'])) {
						//$post_data['have_webpage_preview'] = true;
						//$post_data['webpage_title'] = $raw_post_data['media']['webpage']['title'];
						//$post_data['webpage_descr'] = $raw_post_data['media']['webpage']['description'];
						$post_data['text'] .= "\n\n" . $raw_post_data['media']['webpage']['title'];
						$post_data['text'] .= "\n" . $raw_post_data['media']['webpage']['description'];
					}
					break;
				case 'messageMediaDocument':
					if(!isset($raw_post_data['media']['document']) || !isset($raw_post_data['media']['document']['mime_type'])) {
						//??? unknown
						break;
					}

					switch($raw_post_data['media']['document']['mime_type']) {
						default:
							//unknown mime_type
							$post_data['type'] = 'document';
							$post_data['document_path'] = $this->getPostMediaUrl($channel_ID, $raw_post_data['id']);
							//find document name
							$attributes = $raw_post_data['media']['document']['attributes'];
							$document_name = 'unnamed.ext'; //by default
							foreach($attributes as $attribute) {
								if(isset($attribute['file_name'])) {
									$document_name = $attribute['file_name'];
								}
							}
							$post_data['document_name'] = $document_name;
							break;
						case 'video/mp4':
							//video
							$post_data['type'] = 'video';
							$post_data['image_url'] = $this->getPostImageURL($channel_ID, $raw_post_data['id'], $post_data['type']);
							break;
					}
					break;
			}
			return $post_data;
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

				$post_data = $this->parsePostType($post, $channel_ID);
				if($post_data['type'] == 'document') {
					//Slightly clumsy handling, can be improved
					$msg->type = 'document';
				}
				$msg->document_path = $post_data['document_path'];
				$msg->document_name = $post_data['document_name'];
				$msg->image_url     = $post_data['image_url'];

				$replacement = ""; //\n
				$msg_text = \App\Utilities::dataFilter(
					$post_data['text'],
					$this->db
				);
				//TODO: filter other vars
				$msg_text = str_replace('\n', "\n", $msg_text);
				$msg_text = str_replace('<br />', $replacement, $msg_text);
				$msg->text = html_entity_decode($msg_text);

				$msg->messenger_from_tag = $this->tag;
				$msg->messenger_from_channel = $channel_ID;

				//if($post_data['have_webpage_preview'] == true) {
				//	$msg->have_webpage_preview = true;
				//	$msg->webpage_title = $post_data['webpage_title'];
				//	$msg->webpage_descr = $post_data['webpage_descr'];
				//}

				$messages[] = $msg;
			}

			return $messages;
		}
		
		function getPostImageURLOLD($channel_ID = '', $postID = 980, $post_type = 'photo'): string {
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
		
		function getPostImageURL($channel_ID = '', $postID = 980, $post_type = ''): string {
			return 'https://tg.i-c-a.su/media/' . $channel_ID . '/' . $postID . '/preview';
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
