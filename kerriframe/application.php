<?php
class KF_Application
{
	public function run() {
		$config = KF::getConfig();
		$router = KF::singleton('router');
		foreach ($config->routes as $k => $v) {
			$router->addRoute($k, $v);
		}

		$router->route();

		$request = $router->request;
		$uri = $router->request_uri;

		$controller = KF::getController($uri, true);
		$depth = substr_count($controller->__objectPath, '/');

		array_splice($request, 0, $depth + 1);

		$action = array_shift($request);
		if ($action === null) {
			$action = 'index';
		}
		$callVar = array(
			$controller,
			$action
		);

		if (!is_callable($callVar)) {
			throw new Exception("Cannot call controller method", 1);
		}
		call_user_func_array($callVar, $request);
		KF::singleton('response')->flush();

	}
}
