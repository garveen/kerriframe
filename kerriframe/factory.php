<?php
/**
 * Class and Function List:
 * Function list:
 * - __construct()
 * - autoload()
 * - &getConfig()
 * - singleton()
 * - getCache()
 * - &getDB()
 * - &pingDB()
 * - registry()
 * - getRegistry()
 * - &getController()
 * - &getModel()
 * - __callStatic()
 * - &getMailer()
 * - getSnsClient()
 * - initApp()
 * - raise()
 * Classes list:
 * - KF_Factory
 */
define("STORE_DEFAULT_NAME", "main");

/**
 * Factory class, generate common objects
 */
class KF_Factory
{
	protected static $_security;

	/**
	 * singleton
	 */
	private function __construct() {
	}

	public static function autoload($name) {
		$name = strtolower(substr(strtr($name, '_', '/') , 3));

		if (is_file($filename = KF_PATH . $name . '.php') || is_file($filename = KF_APP_PATH . $name . '.php')) {
			require ($filename);
		}
		if (is_file(KF_APP_PATH . 'core/' . $name . '.php')) {
			require (KF_APP_PATH . 'core/' . $name . '.php');
		}
	}

	private static $_config = null;
	private static $_mailer = null;
	private static $_user = null;
	private static $_sys_config = null;

	/**
	 * Get the config instance, loaded from KF_APP_PATH/config.php
	 *
	 * @return An instance of KF_Config
	 */
	public static function &getConfig($name = null) {
		if (self::$_config == null) {
			self::$_config = new KF_Config;
			require (KF_APP_PATH . 'config.php');
			foreach ($config as $k => $v) {
				self::$_config->$k = $v;
			}
		}
		if ($name === null) {
			return self::$_config;
		} else {
			return self::$_config->$name;
		}
	}

	/**
	 * Return the singleton, or generate if it's not exists.
	 * @param  string  $name
	 * @param  array   $params If set, call the instance's "init" method
	 * @param  boolean $dig    Dig the path
	 * @param  boolean $core   Search class from Kerriframe or user space
	 */
	public static function singleton($name, $params = null, $dig = false, $core = true) {

		$storeName = str_replace('/', '_', $name);
		if (isset(self::$_registry[$storeName])) {
			return self::$_registry[$storeName];
		}
		if ($core) {
			$base_path = KF_PATH;
		} else {
			$base_path = KF_APP_PATH;
		}

		if (!$dig) {
			$pathArr = [
				$name
			];
		} else {
			$pathArr = explode('/', $name);
		}
		$path = '';
		do {
			$path .= array_shift($pathArr);
			$filename = $base_path . $path . '.php';
			if (is_file($filename)) {
				$storeName = str_replace('/', '_', $path);
				if ($core && is_file(KF_APP_PATH . 'core/' . $path . '.php')) {
					$className = KF::getConfig('class_prefix') . $storeName;
				} else {
					$className = 'KF_' . $storeName;
				}

				$obj = new $className;

				$obj->__objectName = $className;
				$obj->__objectPath = $path;

				if (method_exists($obj, 'init')) {
					if ($params === null) $params = array();
					call_user_func_array([$obj, 'init'] , $params);
				}

				self::$_registry[$storeName] = $obj;
				return $obj;
			}

			$path .= '/';
		}
		while (!empty($pathArr));
		throw new KF_Exception("Class {$name} Not Found ");
	}

	/**
	 * Get a cache singleton
	 * @param  string $handler
	 * @param  string $store   store name
	 */
	public static function getCache($handler = 'memcache', $store = STORE_DEFAULT_NAME) {
		return KF_Cache_Cache::singleton($handler, $store);
	}

	private static $_database_connection_pool = array();

	/**
	 * Get a database singleton
	 *
	 * @param String $dbo_name
	 * @param boolen $forceReconnect
	 */
	public static function &getDB($dbo_name = STORE_DEFAULT_NAME, $forceReconnect = false) {

		$dbo = & self::$_database_connection_pool[$dbo_name];
		if (!$forceReconnect && !empty($dbo)) {
			return $dbo;
		}

		$db_config = self::getConfig()->database[$dbo_name];
		$dbo = new KF_Database_Dbo($db_config['url'] , $db_config['user'] , $db_config['pass'] , isset($db_config['options']) ? $db_config['options'] : null);
		$dbo->name = $dbo_name;

		self::$_database_connection_pool[$dbo_name] = $dbo;

		return $dbo;
	}

	/**
	 * ping the database; if failed, reconnect
	 * @param  String $dbo_name
	 */
	public static function &pingDB($dbo_name = STORE_DEFAULT_NAME) {
		$dbo = & self::getDB($dbo_name);
		if ($dbo->ping()) {
			return $dbo;
		} else {
			return self::getDB($dbo_name, true);
		}
	}

	private static $_registry = array();

	/**
	 * register a variable to $_registry
	 *
	 * @param String $key
	 * @param mixed  $value
	 */
	public static function registry($key, $value) {
		self::$_registry[$key] = $value;
	}

	/**
	 * fetch from registry
	 *
	 * @param String $key
	 * @return Object
	 */
	public static function getRegistry($key) {
		return self::$_registry[$key];
	}

	/**
	 * Get the controller singleton
	 *
	 * @param String $name
	 * @return Controller Object
	 */
	public static function &getController($name, $dig = false) {
		try {
			$controller = self::singleton("controller/{$name}", null, $dig, false);
			return $controller;
		}
		catch(Exception $e) {
			self::raise(new KF_Exception("Controller {$name} Not Found"));
		}
	}

	/**
	 * Get the model singleton
	 *
	 * @param String $name
	 * @return Model Object
	 */
	public static function &getModel($name) {
		try {
			$model = self::singleton("model/{$name}", null, false, false);
			return $model;
		}
		catch(Exception $e) {
			self::raise(new KF_Exception("Model {$name} Not Found"));
		}
	}

	public static function __callStatic($name, $args) {
		if (substr($name, 0, 3) == 'get') {
			$className = strtolower(substr($name, 3) . '/' . array_shift($args));
			return self::singleton($className, $args, false, false);
		} else {
			self::raise(new KF_Exception("Undefined method KF::{$name}") , 500);
		}
	}

	/**
	 * 获取 mailer 对象
	 *
	 * @return Mailer 对象
	 */
	public static function &getMailer() {
		if (self::$_mailer === null) {
			require (ES_ROOT . '/utilities/phpmailer/class.phpmailer.php');
			require (ES_ROOT . '/utilities/phpmailer/class.smtp.php');
			self::$_mailer = new PHPMailer;
			self::$_mailer->SetLanguage('zh', ES_ROOT . '/utilities/phpmailer/language/');

			$conf = KF::getConfig();

			$conf->mail_settings['isSMTP'] && self::$_mailer->IsSMTP();

			self::$_mailer->Host = $conf->mail_settings['host'];
			self::$_mailer->Sendmail = $conf->mail_settings['sendMailPath'];
			self::$_mailer->CharSet = 'utf-8';
			self::$_mailer->SMTPAuth = $conf->mail_settings['SMTPAuth'];
			self::$_mailer->Username = $conf->mail_settings['username'];
			self::$_mailer->Password = $conf->mail_settings['password'];
			self::$_mailer->From = $conf->mail_settings['from'];
			self::$_mailer->FromName = $conf->mail_settings['fromName'];
			self::$_mailer->WordWrap = 60;
		}

		return self::$_mailer;
	}

	/**
	 * 获得操作开放平台的Client类
	 * @param string $platfrom qzone,sina
	 * @param string $accessToken 某些不需要登录的api不应该填写accessToken
	 * modified by rur 2012-07-12
	 */
	public static function getSnsClient($platform, $accessToken = null) {
		require_once ES_ROOT . "/utilities/sns/client.php";
		require_once ES_ROOT . "/utilities/oauth2/BaseOauth2.php";
		require_once ES_ROOT . "/utilities/oauth2/Oauth2.php";
		require_once ES_ROOT . '/utilities/oauth2/Client.php';
		return new Client($platform, $accessToken);
	}
	private static $yarClients = array();

	// public static function getYar($rpc_name = STORE_DEFAULT_NAME, $postfix = '') {
	// 	if (isset(self::$yarClients[$rpc_name . $postfix])) {
	// 		return self::$yarClients[$rpc_name . $postfix];
	// 	}
	// 	require_once ES_ROOT . '/utilities/yar.php';

	// 	$yarClient = new NZYar(self::getConfig()->yar_servers[$rpc_name] . $postfix);
	// 	self::$yarClients[$rpc_name] = $yarClient;
	// 	return $yarClient;
	// }



	/**
	 * skeleton generator
	 * @param  string $dirname User space's dir name
	 */
	public static function initApp($dirname = 'app') {
		self::loadOnce('appgen');
		Appgen::init($dirname);
	}

	/**
	 * raise an error
	 * @param  Exception $e
	 * @param  int        http_status http status code
	 */
	public static function raise($e, $http_status = 404) {
		if (KF::getConfig('environment') == 'debug') {
			throw new Exception('Thrown by debug', 1, $e);
		}
		if ($e instanceof Exception) {
			KF::log($e, 'error');
		}
		$routes = KF::getConfig('routes');
		if (isset($routes[$http_status . '_override']) && $routes[$http_status . '_override'] != '') {
			$application = KF::singleton('application');
			$application->dispatch($routes[$http_status . '_override']);
			exit;
		} else {
			http_response_code($http_status);
			echo '<h1>Something Wrong</h1>';
			exit;
		}
	}
}

/* End of file */
