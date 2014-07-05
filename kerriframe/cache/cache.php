<?php
/**
 * Class and Function List:
 * Function list:
 * - __construct()
 * - singleton()
 * - &redis()
 * - &memcache()
 * - &dummy()
 * Classes list:
 * - KF_Cache_Cache
 */
class KF_Cache_Cache
{
	private static $_config;
	private function __construct() {
	}
	private static $_pool = array();
	public static function singleton($handler, $store) {
		if (!isset(self::$_pool[$handler][$store])) {
			$ins = self::$handler($store);
			$className = "KF_Cache_{$handler}";
			self::$_pool[$handler][$store] = new $className($ins);
		}
		return self::$_pool[$handler][$store];
	}

	private static $_redis_pool = array();

	/**
	 * 获取 redis 对象
	 *
	 * @param String $store_name redis STORE的名字
	 * @return redis object or throw
	 */
	public static function &redis($store_name = STORE_DEFAULT_NAME) {

		//先装载系统配置文件
		if (empty(self::$_config)) {
			self::$_config = KF::getConfig();
		}
		$redis = @self::$_redis_pool[$store_name];
		if (empty($redis)) {
			$redis_servers = self::$_config->redis[$store_name];
			if (empty($redis_servers)) {
				throw new KF_Exception("Can't find configure of (" . $store_name . ") redis servers !");
			}
			if (count($redis_servers) > 0) {
				$redis = new Redis();
				$ok = false;
				foreach ($redis_servers as $redis_server_info) {
					if ($redis->connect($redis_server_info['host'] , $redis_server_info['port'])) {
						$redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
						$ok = true;
					}
					if ($ok) {
						break;
					}
				}
				if (!$ok) {

					throw new KF_Exception("Can't connect to (" . $store_name . ") any Redis Servers !");
				}
			}
			self::$_redis_pool[$store_name] = $redis;
		}
		return $redis;
	}

	private static $_memcache_pool = array();

	/**
	 * 获取 memcache 对象
	 *
	 * @param String $store_name memcache的STORE名字
	 * @return memcache对象
	 */
	public static function &memcache($store_name = STORE_DEFAULT_NAME) {

		if (!isset(self::$_memcache_pool[$store_name])) {
			$memcache_servers = KF::getConfig()->memcached[$store_name];
			if (empty($memcache_servers)) {
				throw new KF_Exception("Can't find configure of (" . $store_name . ") memcached servers !");
			}
			if (count($memcache_servers) > 0) {
				$memcache = new Memcache;
				$ok_count = 0;
				foreach ($memcache_servers as $memcached_server_info) {
					if ($memcache->addServer($memcached_server_info['host'] , $memcached_server_info['port'])) $ok_count++;
				}
				if ($ok_count == 0) {
					throw new KF_Exception("Can't connect to (" . $store_name . ") any Memcached Servers !");
				}
			}
			self::$_memcache_pool[$store_name] = $memcache;
		}
		return self::$_memcache_pool[$store_name];
	}

	public static function &dummy() {
		return new stdClass();
	}
}

