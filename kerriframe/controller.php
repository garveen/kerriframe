<?php
abstract class KF_Controller
{
	public $_controllerName = '';
	private $need_redirect = 0;

	public function init() {
		$this->_controllerName = $this->__objectName;
	}

	//直接输出时候使用
	public function raw($data = null, $type = 'json') {
		switch ($type) {
			case 'json':
				$output = json_encode($data, JSON_UNESCAPED_UNICODE);
				break;

			case 'xml':
				$output = KF::singleton('library/xml')->getXml($data);
				break;
		}
		KF::singleton('response')->setContent($output);
	}

	public function display($action_template = null, $vars = array() , $returnOutput = false) {

		ob_start();
		$fileName = KF_APP_PATH . 'view/' . $action_template . '.php';
		if (is_file($fileName)) {
			extract($vars);
			require ($fileName);
		} else {
			throw new Exception("View {$action_template} Not Found", 1);
		}
		if ($returnOutput) {
			return ob_get_clean();
		} elseif (ob_get_level()) {
			ob_flush();
		} else {
			KF::singleton('response')->setContent(ob_get_clean());
		}
	}

	public function redirect($link, $message = null) {
		if (!preg_match('#^https?://#i', $link)) {
			$link = KF::siteUrl($link);
		}
		if (!headers_sent() && empty($message)) {
			header('Location:' . $link);
			exit;
		} else {
			if ($message) {
				echo '<script type="text/javascript">alert("' . $message . '");</script>';
			}
			echo '<script type="text/javascript">window.location.href="' . $link . '";</script>';
		}
	}

	/**
	 * 从 $this->params 里面获取参数，可以支持缺省值
	 *
	 * @param String $name 参数名
	 * @param String $default_value 缺省值
	 * @return String 参数值
	 */
	public static function getParameter($name, $default_value = "") {
		if (array_key_exists($name, $this->params)) {
			return $this->params[$name];
		} else return $default_value;
	}

	/**
	 * 从 $this->params 里面获取 整数型参数，支持缺省值
	 *
	 * @param String $name 参数名
	 * @param int $default_value 缺省值
	 * @return int 参数值
	 */
	public static function getIntParameter($name, $default_value = 0) {
		if (array_key_exists($name, $this->params)) {
			$value = $this->params[$name];
			if (is_int($value)) return intval($value);
			else return $default_value;
		} else return $default_value;
	}
}
