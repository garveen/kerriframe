<?php
/**
* Class and Function List:
* Function list:
* - __construct()
* - match()
* Classes list:
* - KF_Route
*/
class KF_Route
{
	protected $_orig;
	protected $_dest;

	public function __construct($orig, $dest) {
		$this->_orig = $orig;
		$this->_dest = $dest;
	}

	public function match($uri) {

		// Convert wild-cards to RegEx
		$key = str_replace(':any', '.+', str_replace(':num', '[0-9]+', $this->_orig));

		$val = $this->_dest;

		// Does the RegEx match?
		if (preg_match('#^' . $key . '$#', $uri)) {

			// Do we have a back-reference?
			if (strpos($val, '$') !== FALSE AND strpos($key, '(') !== FALSE) {
				$val = preg_replace('#^' . $key . '$#', $val, $uri);
			}

			return $val;
		}

		return false;
	}
}
