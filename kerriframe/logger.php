<?php
class KF_Logger
{

	protected $_log_path;
	protected $_threshold = 1;
	protected $_date_fmt = 'Y-m-d H:i:s';
	protected $_enabled = TRUE;
	protected $_levels = array('ERROR' => '1', 'DEBUG' => '2', 'INFO' => '3', 'ALL' => '4');

	/**
	 * Constructor
	 */
	public function __construct() {
		$config = KF::getConfig();
		$this->_log_path = ($config->log_path != '') ? $config->log_path : KF_APP_PATH . 'log/';

		if (!is_dir($this->_log_path)) {
			$this->_enabled = FALSE;
		}

		$this->_threshold = $this->_levels[strtoupper($config->log_threshold)];

		if ($config->log_date_format != '') {
			$this->_date_fmt = $config->log_date_format;
		}
	}

	// --------------------------------------------------------------------



	/**
	 * Write Log File
	 *
	 * Generally this function will be called using the global log_message() function
	 *
	 * @param	string	the error level
	 * @param	string	the error message
	 * @param	bool	whether the error is a native PHP error
	 * @return	bool
	 */
	public function log($msg, $level = 'error') {
		if ($this->_enabled === FALSE) {
			return FALSE;
		}

		$level = strtoupper($level);

		if (!isset($this->_levels[$level]) OR ($this->_levels[$level] > $this->_threshold)) {
			return FALSE;
		}

		$filepath = $this->_log_path . 'log-' . date('Y-m-d') . '.php';
		$message = '';

		if (!file_exists($filepath)) {
			$message.= "<" . "?php  if ( ! defined('KF_PATH')) exit('No direct script access allowed'); ?" . ">\n\n";
		}

		if (!$fp = @fopen($filepath, 'ab')) {
			return FALSE;
		}
		if(!is_string($msg)) {
			$msg = print_r($msg, true);
		}
		$message.= $level . ' ' . (($level == 'INFO') ? ' -' : '-') . ' ' . date($this->_date_fmt) . ' --> ' . $msg . "\n";

		flock($fp, LOCK_EX);
		fwrite($fp, $message);
		flock($fp, LOCK_UN);
		fclose($fp);

		@chmod($filepath, 0666);
		return TRUE;
	}
}
