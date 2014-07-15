<?php
/**
 * Class and Function List:
 * Function list:
 * - __construct()
 * - init()
 * - log()
 * - header()
 * - baseUrl()
 * - siteUrl()
 * - get()
 * - post()
 * - cookie()
 * - setcookie()
 * - input()
 * - getClientIP()
 * Classes list:
 * - KF extends KF_Factory
 */
define('KF_PATH', __DIR__ . '/');

require (KF_PATH . 'factory.php');

class KF extends KF_Factory
{
	private function __construct() {
	}

	private static $ip = false;

	protected static $_GET;
	protected static $_POST;
	protected static $_COOKIE;
	protected static $_security;

	public static function init() {
		spl_autoload_register(['KF', 'autoload']);
		self::$_security = self::singleton('library/security');

		$config = self::getConfig();

		self::$_GET = $_GET;
		self::$_POST = $_POST;
		self::$_COOKIE = $_COOKIE;

		if ($config->unset_GET_POST) {
			unset($_GET);
			unset($_POST);
		}
	}

	protected static $_logger = false;
	public static function log($message, $level = 'info') {
		if (!self::$_logger) {
			self::$_logger = self::singleton('logger');
		}
		self::$_logger->log($message, $level);
	}

	// shortcuts
	public static function header($k, $v) {
		return parent::singleton('response')->header($k, $v);
	}

	public static function baseUrl($uri = '', $host = false) {
		return parent::singleton('router')->base_url($uri, $host);
	}

	public static function siteUrl($uri = '', $host = false) {
		return parent::singleton('router')->site_url($uri, $host);
	}

	public static function renderWidget($name, $params = array(), $template = 'index') {
		$widget = self::getWidget($name, $params);
		$widget->display($template);
	}

	/**
	 * Fetch an item from the GET array
	 * @param  string   $name
	 * @param  boolean $xss_clean
	 */
	public static function get($name = null, $xss_clean = false) {
		return self::input($name, $xss_clean, '_GET');
	}

	/**
	 * Fetch an item from the POST array
	 * @param  string  $name
	 * @param  boolean $xss_clean
	 */
	public static function post($name = null, $xss_clean = false) {
		return self::input($name, $xss_clean, '_POST');
	}

	public static function cookie($name = null, $xss_clean = false) {
		return self::input($name, $xss_clean, '_COOKIE');
	}

	public static function setcookie($name, $value, $expire = 0) {
		$cookieConfig = KF::getConfig('cookie');
		$domain = $cookieConfig['domain'];
		setcookie($name, $value, $expire, $cookieConfig['path'] , $cookieConfig['domain']);
	}

	protected static function input($name, $xss_clean, $type) {
		$arr = self::$$type;
		if ($name === null) {
			$ret = $arr;
		} elseif (isset($arr[$name])) {
			$ret = $arr[$name];
		} else {
			return false;
		}

		if ($xss_clean) {
			$ret = self::$_security->xss_clean($ret);
		}
		return $ret;
	}

	public static function getClientIP() {
		if (self::$ip !== false) {
			return self::$ip;
		}

		$cip = (isset($_SERVER['HTTP_CLIENT_IP']) AND $_SERVER['HTTP_CLIENT_IP'] != "") ? $_SERVER['HTTP_CLIENT_IP'] : FALSE;
		$rip = (isset($_SERVER['REMOTE_ADDR']) AND $_SERVER['REMOTE_ADDR'] != "") ? $_SERVER['REMOTE_ADDR'] : FALSE;
		$fip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND $_SERVER['HTTP_X_FORWARDED_FOR'] != "") ? $_SERVER['HTTP_X_FORWARDED_FOR'] : FALSE;

		if ($cip && $rip) self::$ip = $cip;
		elseif ($rip) self::$ip = $rip;
		elseif ($cip) self::$ip = $cip;
		elseif ($fip) self::$ip = $fip;

		if (strpos(self::$ip, ',') !== FALSE) {
			$x = explode(',', self::$ip);
			self::$ip = end($x);
		}

		if (!preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", self::$ip)) {
			self::$ip = '0.0.0.0';
		}

		unset($cip);
		unset($rip);
		unset($fip);

		return self::$ip;
	}

	public static function loadSession() {
		self::singleton('library/session');
	}
}

KF::init();
