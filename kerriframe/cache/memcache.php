<?php
/**
* Class and Function List:
* Function list:
* - __construct()
* - getInfo()
* - get()
* - set()
* - add()
* - increment()
* - decrement()
* - delete()
* - replace()
* - flush()
* Classes list:
* - KF_Cache_Memcache
*/
class KF_Cache_Memcache implements KF_Cache_Interface
{
	private $count = 0;
	private $bytes = 0;
	private $gets = array();
	private $sets = array();
	private $debug = false;
	public function __construct($memcache) {
		$this->debug = KF::getConfig()->environment == 'debug';
		$this->_memcache = $memcache;
	}
	public function getInfo() {
		return array(
			'count' => $this->count,
			'bytes' => $this->bytes,
			'gets' => $this->gets,
			'sets' => $this->sets
		);
	}
	public function get($key) {
		$ret = $this->_memcache->get($key);
		if ($this->debug) {
			$this->gets[] = $key;
			$this->count++;
			$this->bytes += strlen(serialize($ret));
		}
		return unserialize($ret);
	}

	public function set($key, $var, $compress = 0, $expire = 86400) {
		if ($this->debug) {
			$this->sets[] = $key;
			$this->count++;
			$this->bytes += strlen(serialize($var));
		}
		return $this->_memcache->set($key, serialize($var) , $compress, $expire);
	}

	public function add($key, $var, $compress = 0, $expire = 0) {
		return $this->_memcache->add($key, $var, $compress, $expire);
	}

	public function increment($key, $value = 1) {
		return $this->_memcache->increment($key, $value);
	}

	public function decrement($key, $value = 1) {
		return $this->_memcache->decrement($key, $value);
	}

	public function delete($key, $timeout = 0) {
		return $this->_memcache->delete($key, $timeout);
	}

	public function replace($key, $var, $compress = 0, $expire = 0) {
		return $this->_memcache->replace($key, $var, $compress, $expire);
	}

	public function flush() {
		return $this->_memcache->flush();
	}
}
?>