<?php
function is_email($email) {
	if (defined('FILTER_VALIDATE_EMAIL')) {
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	} else {
		return (bool)preg_match('#^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$#', $email);
	}
}

function is_telephone($telephone) {
	return (bool)preg_match('#^[0-9]{11}$#', $telephone);
}
