<?php
/**
 * Class and Function List:
 * Function list:
 * - display()
 * - __display()
 * Classes list:
 * - KF_Widget
 */
abstract class KF_Widget
{
	public $default = [];

	public function display($action_template = null, $vars = array() , $returnOutput = false) {
		$action_template = strtolower($this->__objectPath . '/' . $action_template);
		ob_start();
		$fileName = KF_APP_PATH . 'view/' . $action_template . '.php';
		if (is_file($fileName)) {
			$this->__display($fileName, $vars);
		} else {
			throw new KF_Exception("View {$action_template} Not Found", 1);
		}
		if ($returnOutput) {
			return ob_get_clean();
		} elseif (ob_get_level()) {
			ob_flush();
		} else {
			KF::singleton('response')->setContent(ob_get_clean());
		}
	}

	final private function __display($fileName, $vars) {
		$vars = (array)$vars;
		extract($vars);
		require ($fileName);
	}
}
