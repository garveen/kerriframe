<?php

class KF_Library_Session {
	public function __construct() {
		session_start();
	}
	public function get($name = null) {
		if($name === null) {
			return $_SESSION;
		} else {
			return $_SESSION[$name];
		}
	}

	public function set($name, $value) {
		$_SESSION[$name] = $value;
	}

	public function set_flashdata($name, $value) {
		$_SESSION['__KF_FLASHDATA'][$name] = $value;
	}

	public function flashdata($name) {
		return isset($_SESSION['__KF_FLASHDATA'][$name]) ? $_SESSION['__KF_FLASHDATA'][$name] : false;
	}
}
