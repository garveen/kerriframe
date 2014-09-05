<?php
/**
 * Class and Function List:
 * Function list:
 * - raw()
 * - redirect()
 * Classes list:
 * - KF_Controller extends KF_Object
 */
abstract class KF_Controller extends KF_Object
{
	private $need_redirect = 0;

	//直接输出时候使用
	public function raw($data = null, $type = 'json') {
		switch ($type) {
			case 'json':
				$output = json_encode($data, JSON_UNESCAPED_UNICODE);
				break;

			case 'xml':
				$output = KF::singleton('library/xml')->getXml($data);
				break;
		}
		KF::singleton('response')->setContent($output);
	}

	public function redirect($link, $message = null) {
		if (!preg_match('#^https?://#i', $link)) {
			$link = KF::siteUrl($link);
		}
		if (!headers_sent() && empty($message)) {
			header('Location:' . $link);
			exit;
		} else {
			if ($message) {
				echo '<script type="text/javascript">alert("' . $message . '");</script>';
			}
			echo '<script type="text/javascript">window.location.href="' . $link . '";</script>';
		}
	}
}
