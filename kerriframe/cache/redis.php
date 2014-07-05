<?php
class KF_Cache_Redis implements KF_CacheManager {
	public function __construct($ins) {
		$this->ins = $ins;
	}
	public function get($key) {
		if (is_array($key)) {
			$mget = $this->ins->mGet($key);
			$value = array();
			foreach ($key as $i => $single_key) {
				$value[$single_key] = $mget[$i];
			}
		} else {
			$value = $this->ins->get($key);
		}
		return $value;
	}

	public function set($key, $var, $compress = 0, $expire = 86400) {
		if ($expire) {
			return $this->ins->set($key, $var) && $this->ins->expire($key, $expire);
		} else {
			return $this->ins->set($key, $var);
		}
	}

	public function add($key, $var, $compress = 0, $expire = 0) {
		return $expire ? $this->ins->setex($key, $expire, $var) : $this->ins->set($key, $var);
	}

	public function increment($key, $value = 1) {
		return $this->ins->incrBy($key, $value);
	}

	public function decrement($key, $value = 1) {
		return $this->ins->decrBy($key, $value);
	}

	public function delete($key, $timeout = 0) {
		return $this->ins->delete($key);
	}

	public function replace($key, $var, $compress = 0, $expire = 0) {
		return $this->ins->getSet($key, $var);
	}

	public function flush() {
		return $this->ins->flushAll();
	}

	public function __call($name, $args) {
		$redis = $this->ins;
		return call_user_func_array(array(
			$redis,
			$name
		) , $args);
	}
}
