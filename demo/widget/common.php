<?php
/**
* Class and Function List:
* Function list:
* - init()
* Classes list:
* - KF_Widget_Common extends KF_Widget
*/
class KF_Widget_Common extends KF_Widget
{
	public $default = [
		'message' => '',
	];
	public function init($params = array()) {
		$params['message'] .= 'rock!';
		return $params;
	}
}
