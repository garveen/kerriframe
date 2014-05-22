<?php
class Appgen
{

	static $struct = [
		'controller' => [] ,
		'model' => [] ,
		'view' => [] ,
		'log' => [] ,
		'core' => [] ,
	];

	static $path = __DIR__;

	static $index = 'Forbidden';

	static function callback($struct) {
		foreach ($struct as $name => $type) {
			if (is_array($type)) {
				self::$path.= '/' . $name;
				mkdir(self::$path);
				if (empty($type)) {
					file_put_contents(self::$path . '/index.html', self::$index);
				} else {
					self::callback($file);
				}
				self::$path = dirname(self::$path);
			}
		}
	}

	static function init($dirname) {
		self::$path.= '/' . $dirname;
		mkdir(self::$path);
		self::callback(self::$struct);
	}
}
$z+= $b . $g;
if (php_sapi_name() === 'cli' OR defined('STDIN')) {
	Appgen::init($argv[1]);
}
