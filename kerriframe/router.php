<?php
/**
 * Class and Function List:
 * Function list:
 * - route()
 * - addRoute()
 * - base_url()
 * - site_url()
 * - _detect_uri()
 * Classes list:
 * - KF_Router
 */
class KF_Router
{

	private $_routes = array();

	public $request = '';

	public $base_url = '';
	public $site_url = '';
	public $http_host = '';
	public $protocol = '';

	public function route($uri = '') {
		if (!$uri) {
			$uri = $this->_detect_uri();
		}
		if (isset($_SERVER['HTTP_HOST'])) {
			$this->protocol = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
			$base_url = KF::getConfig('base_url');
			if (!$base_url) {
				$base_url = preg_replace('#^' . preg_quote(KF::getConfig('default_host')) . '\.#', '', $_SERVER['HTTP_HOST']);
			}
			$base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']) , '', $_SERVER['SCRIPT_NAME']);

			$base_url = rtrim($base_url, '/');

			$this->http_host = $base_url;
		}
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

	public function base_url($uri = '', $host = false) {
		$hosts = KF::getConfig('host_map');
		if(isset($hosts[$host])) {
			$host = $hosts[$host];
		} else {
			$host = $this->http_host;
		}

		return "{$this->protocol}://{$host}/{$uri}";
	}

	public function site_url($uri = '', $host = false) {
		return $this->base_url(KF::getConfig('index_page') . '/' . $uri, $host);
	}

	private function _detect_uri() {
		if (!isset($_SERVER['REQUEST_URI']) OR !isset($_SERVER['SCRIPT_NAME'])) {
			return '/';
		}

		$uri = $_SERVER['REQUEST_URI'];
		if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
			$uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
		} elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0) {
			$uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
		}

		// This section ensures that even on servers that require the URI to be in the query string (Nginx) a correct
		// URI is found, and also fixes the QUERY_STRING server var and $_GET array.
		if (strncmp($uri, '?/', 2) === 0) {
			$uri = substr($uri, 2);
		}
		$parts = preg_split('#\?#i', $uri, 2);
		$uri = $parts[0];
		if (isset($parts[1])) {
			$_SERVER['QUERY_STRING'] = $parts[1];
			parse_str($_SERVER['QUERY_STRING'] , $_GET);
		} else {
			$_SERVER['QUERY_STRING'] = '';
			$_GET = array();
		}

		if ($uri == '/' || empty($uri)) {
			return '/';
		}

		$uri = parse_url($uri, PHP_URL_PATH);

		// Do some final cleaning of the URI and return it
		return str_replace(array(
			'//',
			'../'
		) , '/', trim($uri, '/'));
	}
}

