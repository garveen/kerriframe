<?php
/**
* Class and Function List:
* Function list:
* - init()
* - index()
* - message()
* Classes list:
* - KF_Controller_Welcome extends MY_Controller
*/
class KF_Controller_Welcome extends MY_Controller
{
	public function init() {
		KF::loadSession();
	}

	public function index() {
		$this->redirect('welcome/message');
	}

	public function message() {
		$nameModel = KF::getModel('name');
		$name = $nameModel->getName();
		$this->display('welcome', [
			'name' => $name
		]);
	}
}
