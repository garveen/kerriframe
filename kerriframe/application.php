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
		$this->dispatch();


	}

	public function dispatch() {
		$router = KF::singleton('router');
		if(!$router->request) {
			KF::raise('router not init', 500);
		}

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

		if(!is_callable($callVar)) {
			KF::raise(new KF_Exception("Cannot call controller method"));
		}
		try {
			$this->callAction($callVar, $request);
		} catch(Exception $e) {
			KF::raise($e, 500);
		}


		KF::singleton('response')->flush();
	}

	public function callAction($callVar, $request) {
		// load the class
		$response = KF::singleton('response');
		ob_start();
		call_user_func_array($callVar, $request);
		$content = ob_get_clean();
		$response->setContent($content);

	}
}
