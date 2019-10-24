<?php
class KLink {
	
	static public function getRoute($url, $encode = true, $secure = NULL) {
		
		$link = KenedoPlatform::p()->getRoute($url, $encode, $secure);
		return $link;
		
	}
	
	static public function base64UrlEncode($input) {
		return strtr(base64_encode($input), '+/=', '-_,');
	}
	
	static public function base64UrlDecode($input) {
		return base64_decode(strtr($input, '-_,', '+/='));
	}
	
}