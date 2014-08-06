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
		'message' => 'Let\'s rock!',
	];
	public function init($params = array()) {
		$this->message = $params['message'];
	}
}
