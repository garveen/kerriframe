<?php

class KF_Cache_Dummy implements KF_CacheManager
{
  public function get( $key )
  {
  	return false;
  }

  public function set( $key, $var, $compress = 0, $expire = 86400 )
  {
  	return false;
  }

  public function add( $key, $var, $compress = 0, $expire = 0 )
  {
  	return false;
  }

  public function increment( $key, $value = 1 )
  {
  	return false;
  }

  public function decrement( $key, $value = 1 )
  {
  	return false;
  }

  public function delete( $key, $timeout = 0 )
  {
  	return false;
  }

  public function replace( $key, $var, $compress = 0, $expire = 0)
  {
  	return false;
  }

  public function flush( )
  {
  	return false;
  }

  public function __call($name, $args) {
  	return false;
  }
}
