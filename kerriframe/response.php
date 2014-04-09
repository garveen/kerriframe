<?php
class KF_Response
{
	private static $headers = array(
		'content-type' => 'text/html; charset=UTF-8'
	);
	private static $body = '';
	private static $html_headers = array();

	public static function header($key, $value, $overwrite = true) {
		$key = strtolower(trim($key));
		if (!$overwrite && isset(self::$headers[$key])) {
			return false;
		}
		self::$headers[] = $value;
		return true;
	}

	public static function setContent($content) {
		self::$body .= $content;
	}

	public static function outputHeader() {
		foreach (self::$headers as $k => $v) {
			header("{$k}:{$v}");
		}
	}

	public static function getContent($name) {
		return self::$body;
	}

	public static function addHtmlHeader($value) {
		self::$html_headers[] = $value;
	}

	public static function getHtmlHeader() {
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
