<?php
/**
* Class and Function List:
* Function list:
* - __construct()
* Classes list:
* - KF_Library_Session
*/
class KF_Library_Session
{
	public function __construct() {
		$sessionConfig = KF::getConfig('session');
		if (isset($sessionConfig['manager']) && $sessionConfig['manager'] && $sessionConfig['manager'] != 'default') {
			$className = 'KF_Library_Session_' . $sessionConfig['manager'];
			new $className($sessionConfig['config']);
		}
		session_start();
	}
}
