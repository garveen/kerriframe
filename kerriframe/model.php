<?php

class KF_Model
{
	protected $_errors = array();

	public function setError($error, $code = 0) {
		if (is_object($error) && (is_subclass_of($error, 'Exception') || $error instanceof Exception)) {
			$this->_errors[] = $error;
		} else {
			$this->_errors[] = new Exception("$error", $code);
		}
	}

	public function getError($toString = true) {
		if (!count($this->_errors)) {
			return false;
		}
		if ($toString) {
			return end($this->_errors)->getMessage();
		} else {
			return end($this->_errors);
		}
	}
}
