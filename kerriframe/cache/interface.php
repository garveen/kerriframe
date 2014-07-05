<?php
/**
* Class and Function List:
* Function list:
* - get()
* - set()
* - add()
* - increment()
* - decrement()
* - delete()
* - replace()
* - flush()
* Classes list:
*/
interface KF_Cache_Interface {
	public function get($key);
	public function set($key, $var, $compress = 0, $expire = 0);
	public function add($key, $var, $compress = 0, $expire = 0);
	public function increment($key, $value = 1);
	public function decrement($key, $value = 1);
	public function delete($key, $timeout = 0);
	public function replace($key, $var, $compress = 0, $expire = 0);
	public function flush();
}

