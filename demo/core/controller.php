<?php
class MY_Controller extends KF_Controller {
	public function display($action_template = null, $vars = array() , $returnOutput = false) {
		return parent::display($action_template, $vars , $returnOutput);
	}
}