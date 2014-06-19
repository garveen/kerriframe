<?php
class KF_Response
{
	public static function __callStatic($name, $args) {
		$instance = KF::singleton('response');
		return call_user_func_array(array($instance, $name), $args);
	}

	private static $headers = array(
		'content-type' => 'text/html; charset=UTF-8'
	);
	private static $body = '';
	private static $html_headers = array();

	public function header($key, $value = '', $overwrite = true) {
		if($value == '') {
			@list($key, $value) = explode(':', $key, 2);
		}
		$key = strtolower(trim($key));
		if (!$overwrite && isset(self::$headers[$key])) {
			return false;
		}
		self::$headers[$key] = $value;
		return true;
	}

	public function setContent($content) {
		self::$body .= $content;
	}

	public function outputHeader() {
		foreach (self::$headers as $k => $v) {
			header("{$k}:{$v}");
		}
	}

	public function getContent($name) {
		return self::$body;
	}

	public function addHtmlHeader($value) {
		self::$html_headers[] = $value;
	}

	public function getHtmlHeader() {
		return self::$content;
	}

	public function outputContent() {
		echo self::$body;
	}

	public function flush() {
		self::outputHeader();
		self::outputContent();
	}
}
