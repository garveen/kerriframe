<?php
class KF_Model_Name extends KF_Model {
	public function getName() {
		$foo = KF::getLibrary('foo');
		return 'Kerriframe';
	}

	public function getSomething() {
		$db = KF::getDB();
		// $db->
	}


}
