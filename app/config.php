<?php
$config = [

	'environment' => 'debug',

	'index_page' => 'index.php',


	/**
	 * If use multi-sub-domains, split your domain into these two configs
	 * Example: main domain is www.example.com
	 *        and you want to use image.example.com
	 *        then config:
	 *             base_url     : example.com
	 *             default_host : www
	 */

	'base_url' => '',

	'default_host' => '',

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
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
				PDO::ATTR_PERSISTENT => false,
			] ,
		] ,
	] ,

	'memcached' => [
		'main' => [
			[
				'host' => 'cacheserver',
				'port' => '11212',
			]
		] ,
	] ,

	'class_prefix' => 'MY_',

	'cookie' => [
		'path' => '/',
		'domain' => '',
	],
];

