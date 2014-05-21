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

		array_splice($request, 0, $depth);

		$action = array_shift($request);
		if ($action === null) {
			$action = 'index';
		}
		$controller->__action = $action;

		if(method_exists($controller, '__preAction')) {
			$controller->__preAction();
		}
		$callVar = array(
			$controller,
			$action
		);

		if (!is_callable($callVar) || $action[0] == '_') {
			throw new Exception("Cannot call controller method", 1);
		}
		// load the class
		$response = KF::singleton('response');
		ob_start();
		call_user_func_array($callVar, $request);
		$content = ob_get_clean();
		$response->setContent($content);
		$response->flush();

	}
}
