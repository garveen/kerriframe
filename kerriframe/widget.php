<?php
/**
 * Class and Function List:
 * Function list:
 * - display()
 * - __display()
 * Classes list:
 * - KF_Widget extends KF_Object
 */
abstract class KF_Widget extends KF_Object
{
	public $default = [];

	public function display($action_template = null, $vars = array() , $returnOutput = false) {
		$action_template = strtolower($this->__objectPath . '/' . $action_template);
		return parent::display($action_template, $vars, $returnOutput);
	}


}
