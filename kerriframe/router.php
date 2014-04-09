<?php
class KF_Router
{

	private $_routes = array();

	public $request = '';

	public $base_url = '';
	public $site_url = '';

	public function route() {

		$uri = trim($_SERVER['PATH_INFO'] , '/');

		$this->base_url = rtrim(str_replace(basename($_SERVER['SCRIPT_NAME']) , '', $_SERVER['SCRIPT_NAME']), '/');
		$this->site_url = $this->base_url . KF::getConfig()->index_page;

		foreach ($this->_routes as $route) {

			if ($params = $route->match($uri)) {
				$this->request_uri = $params;
				$this->request = explode('/', $params);
				return;
			}
		}
		$this->request_uri = $uri;
		$this->request = explode('/', $uri);
	}

	public function addRoute($orig, $dest) {
		$this->_routes[] = new KF_Route($orig, $dest);
	}

	public function base_url($uri = '') {
		return "{$this->base_url}/{$uri}";
	}

	public function site_url($uri = '') {
		return "{$this->site_url}/{$uri}";
	}
}

class KF_Route
{
	protected $_orig;
	protected $_dest;

	public function __construct($orig, $dest) {
		$this->_orig = $orig;
		$this->_dest = $dest;
	}

	public function match($uri) {

		// Convert wild-cards to RegEx
		$key = str_replace(':any', '.+', str_replace(':num', '[0-9]+', $this->_orig));

		$val = $this->_dest;

		// Does the RegEx match?
		if (preg_match('#^' . $key . '$#', $uri)) {

			// Do we have a back-reference?
			if (strpos($val, '$') !== FALSE AND strpos($key, '(') !== FALSE) {
				$val = preg_replace('#^' . $key . '$#', $val, $uri);
			}

			return $val;
		}

		return false;
	}
}

