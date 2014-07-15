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
	public function __construct($config) {

		//不使用 GET/POST 变量方式
		ini_set('session.use_trans_sid', 0);

		//设置垃圾回收最大生存时间
		ini_set('session.gc_maxlifetime', $config['expire']);

		//使用 COOKIE 保存 SESSION ID 的方式
		ini_set('session.use_cookies', 1);
		ini_set('session.cookie_path', '/');

		//多主机共享保存 SESSION ID 的 COOKIE
		$domain = KF::getConfig('cookie') ['domain'];
		ini_set('session.cookie_domain', $domain);

		ini_set('session.serialize_handler', 'php_serialize');
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
