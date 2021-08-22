<?php
class KRequest {

	static protected $purifierConfig = NULL;

	static function getString($key, $default = NULL, $from = 'METHOD') {
		$value = self::getValue($key, $default, $from, 'string');
		return $value;
	}

	static function getArray($key, $default = array(), $from = 'METHOD') {
		$value = self::getValue($key, $default, $from, 'array');
		return $value;
	}

	static function getFile($key, $default = array()) {
		$array = !empty($_FILES[$key]['tmp_name']) ? $_FILES[$key] : $default;
		return $array;
	}

	static function getKeyword($key, $default = NULL, $from = 'METHOD') {
		$value = self::getValue($key, $default, $from, 'string');
		$value = str_replace(' ', '', $value);
		return $value;
	}

	static function getHtml($key, $default = NULL, $from = 'METHOD') {
		$value = self::getValue($key, $default, $from, 'html');
		return $value;
	}

	/**
	 * @param $key
	 * @param int|null $default
	 * @param string $from
	 * @return int
	 */
	static function getInt($key, $default = NULL, $from = 'METHOD') {
		$value = self::getValue($key, $default, $from, 'int');
		return $value;
	}

	static function getFloat($key, $default = NULL, $from = 'METHOD') {
		$value = self::getValue($key, $default, $from, 'float');
		return $value;
	}

	static function getVar($key, $default = NULL, $from = 'METHOD', $sanitationType = 'raw') {
		$value = self::getValue($key, $default, $from, $sanitationType);
		return $value;
	}

	static function setVar($key, $value, $from = 'METHOD') {

		if ($from == 'METHOD') {
			$from = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : '';
		}
		if ($from == 'POST') {
			$_POST[$key] = $value;
		}
		if ($from == 'GET') {
			$_GET[$key] = $value;
		}
		$_REQUEST[$key] = $value;

	}

	static protected function getValue($key, $default, $from, $sanitationType) {

		$from = self::getFrom($from);

		$value = isset($from[$key]) ? $from[$key] : NULL;

		if ($value === NULL) {
			return $default;
		}

		if (ini_get('magic_quotes_gpc')) {
			$value = self::stripSlashes($value);
		}

		switch ($sanitationType) {
			case 'int':
				$sanitized = (int)$value;
				break;

			case 'float':
				$sanitized = (float)$value;
				break;

			case 'string':
				$sanitized = filter_var ( $value, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES );
				break;

			case 'html':
				$sanitized = self::filterHtml($value);
				break;

			case 'raw':
				$sanitized = $value;
				break;

			case 'array':
				if (!is_array($value)) {
					$value = array($value);
				}
				$sanitized = $value;
				break;

			default:
				KLog::log('Unknown sanitationType "'.$sanitationType.'" selected. Returning nothing.', 'error');
				$sanitized = '';

		}

		return $sanitized;

	}

	/**
	 * @param string $from GET/POST Defaults to METHOD, giving you REQUEST
	 * @return array
	 */
	static public function getAll($from = 'METHOD') {
		return self::getFrom($from);
	}

	static protected function &getFrom($from) {
		$from = strtoupper( $from );
		if ($from == 'METHOD') {
			$from = 'REQUEST';
		}

		switch ($from) {

			case 'POST':
				$return =& $_POST;
				break;

			case 'GET':
				$return =& $_GET;
				break;

			case 'COOKIE':
				$return =& $_COOKIE;
				break;

			case 'REQUEST':
				$return =& $_REQUEST;
				break;

			case 'FILES':
				$return =& $_FILES;
				break;

			default:
				$return =& $_REQUEST;
				break;
		}

		if (KenedoPlatform::getName() == 'wordpress') {
			$return = self::stripSlashes($return);
		}

		return $return;
	}

	static protected function filterHtml($value) {

		// Skip loading HTMLPurifier and filtering if there isn't anything
		if (trim($value) == '') {
			return $value;
		}

		if (!self::$purifierConfig) {

			require_once (dirname(__FILE__).'/../external/htmlpurifier/HTMLPurifier.auto.php');

			$config = HTMLPurifier_Config::createDefault();
			$config->set('Cache.SerializerPath', KenedoPlatform::p()->getDirDataStore().'/private/htmlpurifier/');
			$config->set('Core.Encoding', 'utf-8');
			$config->set('Core.RemoveScriptContents', true);

			$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
			$config->set('HTML.TidyLevel', 'medium');
			$config->set('HTML.Allowed', null);
			$config->set('Cache.DefinitionImpl', null);
			$config->set('HTML.SafeIframe', true);
			$config->set('URI.SafeIframeRegexp', '%//%');



			$config->set('Attr.EnableID', true);

			$config->set('HTML.Trusted', false);
			$config->set('CSS.Proprietary', true );

			$config->set('Attr.AllowedFrameTargets', array(
				0=>'_self',
				1=>'_blank',
				2=>'_parent',
				3=>'_top'
			));

			$def = $config->getHTMLDefinition(true);

			$def->addElement('video', 'Block', 'Optional: #PCDATA | Flow | source', false, array(
				'width' => 'Text',
				'height' => 'Text',
				'src' => 'Text',
				'controls' => 'Text',
				'type' => 'Text',
				'track' => 'Text',
				'poster' => 'Text',
			));

			$def->addElement('audio', 'Block', 'Inline', false, array(
				'width' => 'Text',
				'height' => 'Text',
				'src' => 'Text',
				'controls' => 'Text',
				'type' => 'Text',
				'track' => 'Text',
				'poster' => 'Text',
			));

			$def->addElement('source', false, 'Empty', false,array(
				'src' => 'Text',
				'type' => 'Text',
			));

			$def->addAttribute('a', 'data-modal-width', 'Number');
			$def->addAttribute('a', 'data-modal-height', 'Number');

			self::$purifierConfig = $config;
		}

		// perform the replacement
		$purifier = new HTMLPurifier(self::$purifierConfig);

		$value = $purifier->purify($value);

		return $value;

	}

	protected static function stripSlashes($value) {
		$value = is_array($value) ? array_map(array('KRequest', 'stripSlashes'), $value) : stripslashes($value);
		return $value;
	}

}