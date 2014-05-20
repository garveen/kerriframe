<?php
define("STORE_DEFAULT_NAME", "main");

class KF_Factory
{
	protected static $_GET;
	protected static $_POST;

	protected static $_security;
	private function __construct() {
	}

	public static function init() {

		self::load('exception');
		self::load('controller');
		self::load('model');
		self::load('functions');

		self::load('database/activerecord');
		self::load('database/dbo');

		self::$_security = self::singleton('library/security');

		$config = self::getConfig();

		self::$_GET = $_GET;
		self::$_POST = $_POST;

		if ($config->unset_GET_POST) {
			unset($_GET);
			unset($_POST);
		}
	}

	private static $_instance = null;

	private static $_config = null;
	private static $_mailer = null;
	private static $_user = null;
	private static $_sys_config = null;

	public static function get($name = null, $xss_clean = false) {
		if($name === null) {
			$ret = self::$_GET;
		} elseif (isset(self::$_GET[$name])) {
			$ret = self::$_GET[$name];
		} else {
			return false;
		}

		if($xss_clean) {
			$ret = self::$_security->xss_clean($ret);
		}
		return $ret;
	}

	public static function post($name = null, $xss_clean = false) {
		if($name === null) {
			$ret = self::$_POST;
		} elseif (isset(self::$_POST[$name])) {
			$ret = self::$_POST[$name];
		} else {
			return false;
		}

		if($xss_clean) {
			$ret = self::$_security->xss_clean($ret);
		}
		return $ret;
	}

	/**
	 * 获取配置文件的配置对象
	 *
	 * @return KF_Config对象
	 */
	public static function &getConfig($name = null) {
		if (self::$_config == null) {
			self::load('config');
			self::$_config = new KF_Config;
			require (KF_APP_PATH . 'config.php');
			foreach ($config as $k => $v) {
				self::$_config->$k = $v;
			}
		}
		if($name === null) {
			return self::$_config;
		} else {
			return self::$_config->$name;
		}
	}

	public static function load($name, $once = false) {
		if(is_file($filename = KF_PATH . $name . '.php') || is_file($filename = KF_APP_PATH . $name . '.php')) {
			if ($once) {
				require_once ($filename);
			} else {
				require ($filename);
			}
		} else {
			throw new Exception("File Not Found");

		}
	}

	public static function singleton($name, $params = null, $dig = false, $core = true) {

		$className = 'KF_' . str_replace('/', '_', $name);
		if (isset(self::$_registry[$className])) {
			return self::$_registry[$className];
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
				require ($filename);
				$className = 'KF_' . str_replace('/', '_', $path);
				$obj = new $className;

				$obj->__objectName = $className;
				$obj->__objectPath = $path;

				if (method_exists($obj, 'init')) {
					if ($params === null) $params = array();
					call_user_func_array([
						$obj,
						'init'
					] , $params);
				}

				self::$_registry[$className] = $obj;
				return $obj;
			}

			$path .= '/';
		}
		while (!empty($pathArr));
		throw new KF_Exception("Class {$name} Not Found ");
	}

	public static function load_once($name) {
		static $cache = [];
		if(isset($cache[$name])) {
			return true;
		} else {
			$cache[$name] = true;
		}
		return self::load($name, true);
	}

	protected static $_cache_init = false;

	public static function getCache($handler = 'memcache', $store = STORE_DEFAULT_NAME) {
		if (!self::$_cache_init) {
			self::load_once('cache/cache');
			self::$_cache_init = true;
		}
		return cacheRegister::singleton($handler, $store);
	}

	private static $_database_connection_pool = array();

	/**
	 * 获取数据库处理对象
	 *
	 * @param String $dbo_name
	 * @param boolen $forceReconnect 是否强制重新连接
	 * @return DBO
	 */
	public static function &getDB($dbo_name = STORE_DEFAULT_NAME, $forceReconnect = false) {

		$dbo = & self::$_database_connection_pool[$dbo_name];
		if (!$forceReconnect && !empty($dbo)) {
			return $dbo;
		}

		$db_config = self::getConfig()->database[$dbo_name];
		$dbo = new KF_DBO($db_config['url'] , $db_config['user'] , $db_config['pass'] , isset($db_config['options']) ? $db_config['options'] : null);
		$dbo->name = $dbo_name;

		self::$_database_connection_pool[$dbo_name] = $dbo;

		return $dbo;
	}

	/**
	 * ping数据库连接；若失败则重新连接
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
	 * 注册一个变量到 registry 区域
	 *
	 * @param String $key
	 * @param Object $value
	 */
	public static function registry($key, $value) {
		self::$_registry[$key] = $value;
	}

	/**
	 * 从 registry 区域获取一个变量的值
	 *
	 * @param String $key
	 * @return Object
	 */
	public static function getRegistry($key) {
		return self::$_registry[$key];
	}

	/**
	 * 获取 controller 对象
	 * 先试图读取 registry 区域，读不到才去 构造
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

	public static function getWidget() {
	}

	// public static function

	/**
	 * 获取 model 对象
	 *
	 * @param String $name model名（英文名，唯一的）
	 * @return Model Object
	 */
	public static function &getModel($name) {
		try {
			$model = self::singleton("model/{$name}", null, false, false);
			return $model;
		}
		catch(Exception $e) {
			self::raise(KF_Exception("Model {$name} Not Found"));
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

	public static function initApp($dirname = 'app') {
		self::load_once('appgen');
		Appgen::init($dirname);
	}

	public static function raise($e) {
		echo $e->getMessage();
		exit;
	}
}

/* End of file */
