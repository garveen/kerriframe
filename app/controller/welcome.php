<?php
class KF_Controller_Welcome extends MY_Controller
{
	public function index() {
		$this->redirect('welcome/message');
	}

	public function message() {
		$nameModel = KF::getModel('name', [
			'Kerriframe'
		]);
		$name = $nameModel->getName();
		$this->display('welcome', [
			'name' => $name
		]);
	}
}
