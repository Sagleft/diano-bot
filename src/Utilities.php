<?php
	namespace App;

	class Utilities {
		public static function isJson($string): bool {
			return ((is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string))))) ? true : false;
		}

		public static function dataFilter($string = "", $db_link = null) {
			//\App\Utilities::dataFilter
			$string = strip_tags($string);
			$string = stripslashes($string);
			$string = htmlspecialchars($string);
			$string = trim($string);
			if(isset($db_link) && $db_link != null) {
				$string = $db_link->filter_string($string);
			}
			return $string;
		}

		public static function cURL($url, $ref, $header, $cookie, $p=null) {
			$ch =  curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			if(isset($_SERVER['HTTP_USER_AGENT'])) {
				curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
			}
			if($ref != '') {
				curl_setopt($ch, CURLOPT_REFERER, $ref);
			}
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			if($cookie != '') {
				curl_setopt($ch, CURLOPT_COOKIE, $cookie);
			}
			if ($p) {
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $p);
			}
			$result =  curl_exec($ch);
			curl_close($ch);
			if ($result){
				return $result;
			} else {
				return '';
			}
		}

		public static function curlGET($url) {
			return Utilities::cURL($url, '', '', '');
		}

		public static function generateString($length = 6, $chars = 'ABCDEF'): string {
			$code = "";
			$clen = strlen($chars) - 1;
			while (strlen($code) < $length) {
				$code .= $chars[mt_rand(0, $clen)];
			}
			return $code;
		}

		public static function generateCode($length = 6): string {
			$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
			return Utilities::generateString($length, $chars);
		}

		public static function generateHEX($length = 8): string {
			return Utilities::generateString($length, 'ABCDEF0123456789');
		}

		//return: mixed
		public static function checkFields($arr = [], $keysArr = [], $errCode = "error", $db_link = null, $ignore_errors = false) {
			$data = [];
			foreach ($keysArr as $key) {
				if(!isset($arr[$key]) || (empty($arr[$key]) && $arr[$key] != "0" && $arr[$key] != 0)) {
					if(!$ignore_errors) {
						exit($errCode.' ('.$key.' is empty)');
					}
				} else {
					$data[$key] = Utilities::dataFilter($arr[$key], $db_link);
				}
			}
			return $data;
		}

		public static function checkINT($value = 0, $db_link = null): int {
			$value = Utilities::dataFilter($value, $db_link) + 0;
			if(!is_int($value)) {
				$value = 0;
			}
			return $value;
		}

		public static function checkFloat($value = 0, $db_link = null): float {
			$value = floatval(Utilities::dataFilter($value, $db_link));
			if(!is_float($value)) {
				$value = 0;
			}
			return $value;
		}

		public static function checkINTFields($arr = [], $keysArr = [], $db_link = null): array {
			//$db_link - ссылка на экземпляр \App\DataBase
			$data = [];
			foreach ($keysArr as $key) {
				if(!isset($arr[$key]) || empty($arr[$key])) {
					$data[$key] = 0;
				} else {
					$data[$key] = Utilities::checkINT($arr[$key], $db_link);
				}
			}
			return $data;
		}

		public static function remoteFileExists($url = 'https://example.com/image.jpg') {
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_NOBODY, true);
			//send request
			$result = curl_exec($curl);
			$exists = false;
			//if request did not fail
			if ($result !== false) {
				//check response code
				$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
				if ($statusCode == 200) {
					$exists = true;
				}
			}
			curl_close($curl);
			return $exists;
		}
	}
