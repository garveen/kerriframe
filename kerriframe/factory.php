<?php
define("STORE_DEFAULT_NAME", "main");

class KF_Factory
{
	protected static $_GET;
	protected static $_POST;
	private function __construct() {
	}

	public static function init() {

		self::load('exception');
		self::load('controller');
		self::load('model');

		self::load('database/dbo');
		self::load('database/activerecord');

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

	public static function get($name, $default = null) {
		if (isset(self::$_GET[$name])) {
			return self::$_GET[$name];
		} else {
			return $default;
		}
	}

	public static function post($name, $default = null) {
		if (isset(self::$_POST[$name])) {
			return self::$_POST[$name];
		} else {
			return $default;
		}
	}

	/**
	 * 获取配置文件的配置对象
	 *
	 * @return KF_Config对象
	 */
	public static function &getConfig() {
		if (self::$_config == null) {
			self::load('config');
			self::$_config = new KF_Config;
			require (KF_APP_PATH . 'config.php');
			foreach ($config as $k => $v) {
				self::$_config->$k = $v;
			}
		}
		return self::$_config;
	}

	public static function load($name, $once = false) {
		if ($once) {
			require_once (KF_PATH . $name . '.php');
		} else {
			require (KF_PATH . $name . '.php');
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
					call_user_func_array(array(
						$obj,
						'init'
					) , $params);
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
	public static function &getDBO($dbo_name = STORE_DEFAULT_NAME, $forceReconnect = false) {

		$dbo = & self::$_database_connection_pool[$dbo_name];
		if (!$forceReconnect && !empty($dbo)) {
			return $dbo;
		}

		$db_config = self::getConfig()->database[$dbo_name];
		$dbo = new KF_DBO($db_config['url'] , $db_config['user'] , $db_config['pass'] , isset($db_config['options']) ? $db_config['options'] : null);
		$dbo->name = $dbo_name;
		$dbo->ar = new KF_DATABASE_activerecord($dbo);

		self::$_database_connection_pool[$dbo_name] = $dbo;

		return $dbo;
	}

	/**
	 * ping数据库连接；若失败则重新连接
	 * @param  String $dbo_name
	 */
	public static function &pingDB($dbo_name = STORE_DEFAULT_NAME) {
		$dbo = & self::getDBO($dbo_name);
		if ($dbo->ping()) {
			return $dbo;
		} else {
			return self::getDBO($dbo_name, true);
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
			throw new KF_Exception("Controller {$name} Not Found");
		}
	}

	public static function getWidget() {
	}

	/**
	 * 获取 model 对象
	 *
	 * @param String $name model名（英文名，唯一的）
	 * @return Model Object
	 */
	public static function &getModel($name, $dig = false) {
		try {
			$model = self::singleton("model/{$name}", null, $dig, false);
			return $model;
		}
		catch(Exception $e) {
			throw new KF_Exception("Model {$name} Not Found");
		}
	}

	// protected static function

	private static $users = array();

	/**
	 * 获取当前用户的对象（从session获取）
	 *
	 * @return user as stdclass
	 */
	public static function getCurrentUser() {
		if (self::isAnonymous()) {
			return null;
		}

		if (self::$_user === null) {
			self::$_user = clone ($_SESSION['userdata']);
			if (self::getUserById(self::$_user->id)->status == 9) {

				// 清除session
				session_unset();
				self::$_user = null;
				return null;
			}
			self::$_user->username = base64_decode(self::$_user->username);
		}

		return self::$_user;
	}

	/**
	 * 设置当前用户的属性表
	 *
	 * @param String $property 属性名
	 * @param String $value 属性值
	 * @return true 表示成功
	 */
	public static function setCurrentUserProperty($property, $value) {
		if (self::isAnonymous()) {
			return false;
		}

		if ($property == 'username') {
			$value = base64_encode($value);
		}

		$_SESSION['userdata']->$property = $value;

		self::$_user = null;

		return true;
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
	 * 发送邮件
	 *
	 * @param String $subject 标题
	 * @param String $body  内容
	 * @param String $sendTo  收件人email地址
	 * @param String $sendToName  收件人名字
	 * @param String $altBody
	 * @param String $isHTML  是否是HTML格式的邮件
	 * @return true 表示成功
	 */
	public static function sendMail($subject, $body, $sendTo, $sendToName = null, $altBody = "", $isHTML = false) {
		$mailer = & self::getMailer();

		$mailer->Subject = $subject;
		$mailer->Body = $body;
		$mailer->AltBody = $altBody;
		$mailer->ClearAddresses();

		//清除邮件列表
		$mailer->AddAddress($sendTo, $sendToName);

		$mailer->IsHTML($isHTML);

		return $mailer->Send();
	}

	/**
	 * 获取模板文件的路径
	 *
	 * @param String $skin_name 皮肤名字
	 * @return 模板的实际路径
	 */
	public static function getTemplatePath($skin_name = null) {
		$path = null;

		if ($path === null) {
			if ($skin_name === null) {
				$path = ES_APP_ROOT . '/templates/' . self::$_config->skin_name . '/';
			} else {
				$path = ES_APP_ROOT . '/templates/' . $skin_name . '/';
			}
		}

		return $path;
	}

	/**
	 * 做 HASH 操作
	 *
	 * @param String $seed 种子
	 * @return MD5 HASH之后的字符串
	 */
	public static function getHash($seed) {
		return md5(@self::$_config->secret . $seed);
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
}

/* End of file */
