<?php
$config = [

	'environment' => 'debug',

	'index_page' => 'index.php',

	'base_url' => '',

	'host_map' => [
		'example' => 'www.example.com',
	],

	/**
	 * error, debug, info
	 */
	'log_threshold' => 'error',

	'routes' => [

		// '(:any)' => 'welcome/$1',
		// '(:num)' => 'welcome/detail/$1',
		// default
		'/' => 'welcome',
		// error pages
		'404_override' => 'error/status_404',
		'500_override' => 'error/status_500',
	] ,

	'unset_GET_POST' => true,

	'default_controller' => '',

	'database' => [
		'main' => [
			'url' => 'mysql:host=localhost;dbname=test',
			'user' => 'root',
			'pass' => '',
			'options' => [
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8MB4',
				PDO::ATTR_PERSISTENT => false,
			] ,
		] ,
	] ,

	'cache_default_handler' => 'memcache',

	'memcached' => [
		'main' => [
			[
				'host' => 'cacheserver',
				'port' => '11211',
			]
		] ,
		'session' => [
			[
				'host' => 'sessionserver',
				'port' => '11211',
			]
		] ,
	] ,

	'class_prefix' => 'MY_',

	'cookie' => [
		'path' => '/',
		'domain' => '',
	] ,

	'session' => [
		'manager' => 'file',
		'config' => [
			'expire' => 1800,
		] ,
	] ,

	'site_dir_map' => [
		'www' => '',
		'kerriframe' => 'gavin',
	] ,
];

