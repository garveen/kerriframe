<?php
/**
 * Class and Function List:
 * Function list:
 * - run()
 * - dispatch()
 * - callAction()
 * Classes list:
 * - KF_Application
 */
class KF_Application
{
	public function run() {
		$config = KF::getConfig();
		$router = KF::singleton('router');
		foreach ($config->routes as $k => $v) {
			$router->addRoute($k, $v);
		}

		$this->dispatch();
	}

	public function dispatch($uri = '') {

		$router = KF::singleton('router');
		$router->route($uri);

		if (!$router->request) {
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

		if ($action[0] == '_') {
			KF::raise('access denied', 404);
		}

		$controller->__action = $action;

		if (method_exists($controller, '_remap')) {
			$controller->_remap($action, $request);
		}

		$callVar = array(
			$controller,
			$action
		);

		if (!is_callable($callVar)) {
			KF::raise(new KF_Exception("Cannot call controller method"));
		}
		ob_start();
		try {
			$this->callAction($callVar, $request);
		}
		catch(Exception $e) {
			KF::raise($e, 500);
		}
		ob_end_flush();

		KF::singleton('response')->flush();
	}

	public function callAction($callVar, $request) {

		// load the class
		$response = KF::singleton('response');
		ob_start();
		$ret = call_user_func_array($callVar, $request);
		$content = ob_get_clean();
		$response->setContent($content);
		if($ret !== null) {
			$response->setResponse($ret);
		}
	}
}
