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
				$msg->text = \App\Utilities::dataFilter(
					str_replace('<br />', $replacement, $post['message']),
					$this->db
				);
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
							//video in post
							$post_type = 'video';
							$msg->image_url = $this->getPostImageURL($channel_ID, $post['id'], $post_type);
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

	}
