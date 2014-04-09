<?php

class KF_RedisCacheManager implements KF_CacheManager
{
  public function get( $key )
  {
  	if(is_array($key)) {
  		$mget = KF::getRedis()->mGet($key);
		$value = array();
		foreach($key as $i => $single_key) {
			$value[$single_key] = $mget[$i];
		}
  	} else {
  		$value = KF::getRedis()->get( $key );
  	}
    return $value;
  }

  public function set( $key, $var, $compress = 0, $expire = 86400 )

  {
  	if($expire) {
  		return KF::getRedis()->setnx( $key, $var) && KF::getRedis()->expire($key, $expire);
  	} else {
    	return KF::getRedis()->setnx($key,$var);
    }
  }

  public function add( $key, $var, $compress = 0, $expire = 0 )
  {
    return $expire? KF::getRedis()->setex( $key, $expire, $var) : KF::getRedis()->set( $key, $var );
  }

  public function increment( $key, $value = 1 )
  {
    return KF::getRedis()->incrBy( $key, $value );
  }

  public function decrement( $key, $value = 1 )
  {
    return KF::getRedis()->decrBy( $key, $value );
  }

  public function delete( $key, $timeout = 0 )
  {
    return KF::getRedis()->delete( $key );
  }

  public function replace( $key, $var, $compress = 0, $expire = 0)
  {
    return KF::getRedis()->getSet( $key, $var );
  }

  public function flush( )
  {
    return KF::getRedis()->flushAll();
  }

  public function __call($name, $args) {
  	$redis = KF::getRedis();
  	return call_user_func_array(array($redis, $name), $args);
  }
}
