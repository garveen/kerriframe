<?php
/**
* Class and Function List:
* Function list:
* - __construct()
* - open()
* - close()
* - read()
* - write()
* - destroy()
* - gc()
* Classes list:
* - KF_Library_Session_Memcache
*/
class KF_Library_Session_Memcache
{
	public function __construct() {

		session_set_save_handler(array(
			$this,
			'open'
		) , array(
			$this,
			'close'
		) , array(
			$this,
			'read'
		) , array(
			$this,
			'write'
		) , array(
			$this,
			'destroy'
		) , array(
			$this,
			'gc'
		));
		$this->cache = KF::getCache('memcache', 'session');
	}

	public function open($save_path, $session_name) {
		return true;
	}

	public function close() {
		return true;
	}

	public function read($key) {
		return $this->cache->get($key);
	}

	public function write($key, $value) {
		return $this->cache->set($key, $value);
	}

	public function destroy($key) {
		return $this->cache->delete($key);
	}

	public function gc($maxlifetime = null) {
		return true;
	}
}
