<?php
class KF_Controller_Error extends MY_Controller
{
	public function __construct() {
		while(ob_get_level()) {
			ob_end_clean();
		}
	}
	public function status_404() {
		KF::singleton('response')->clean();
		$this->display('404');
	}

	public function status_500() {
		KF::singleton('response')->clean();
		$this->display('500');
	}
}
