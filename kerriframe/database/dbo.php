<?php
/**
 * Class and Function List:
 * Function list:
 * - __call()
 * - __construct()
 * - _init()
 * - prepare()
 * - query()
 * - quote()
 * - is_write_type()
 * - lastInsertId()
 * - replacePrefix()
 * - getQueries()
 * - getErrorMsg()
 * - ping()
 * Classes list:
 * - KF_Database_Dbo extends KF_Database_activerecord
 */
class KF_Database_Dbo extends KF_Database_activerecord
{
	public static $qeuries = array();
	public $name;
	private $params = array();
	private $pdo = false;
	public $debug = false;
	public function __call($name, $args) {
		if (!$this->pdo) {
			$this->_init();
		}
		return call_user_func_array(array(
			$this->pdo,
			$name
		) , $args);
	}
	public function __construct($dsn, $username, $password, $options = null) {

		$this->trans_enabled = FALSE;

		$this->_random_keyword = ' RND(' . time() . ')';

		// database specific random keyword

		$this->params = array(
			'dsn' => $dsn,
			'username' => $username,
			'password' => $password,
			'options' => $options
		);
		$this->debug = KF::getConfig()->environment == 'debug';
	}

	private function _init() {
		$params = $this->params;
		$this->pdo = new PDO($params['dsn'] , $params['username'] , $params['password'] , $params['options']);

		// 设置错误报告模式 如果执行失败且不在debug模式，将捕获并写log
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public function prepare($sql, $driver_options = array()) {
		if (!$this->pdo) {
			$this->_init();
		}
		$sql_after_render = $this->replacePrefix($sql);
		self::$qeuries[] = $sql_after_render;
		return $this->pdo->prepare($sql_after_render, $driver_options);
	}

	public function query($sql, $params = array()) {
		$sql = ltrim($sql);

		if (!$this->pdo) {
			$this->_init();
		}

		if (is_object($params)) {
			$params = (array)$params;
		}

		if (!is_array($params)) {
			$params = [
				$params
			];
		}

		$sql_after_render = $this->replacePrefix($sql);
		try {
			if (empty($params)) {
				$stmt = $this->pdo->query($sql_after_render);
			} else {
				$stmt = $this->pdo->prepare($sql_after_render);
				$stmt->execute($params);
			}
		}
		catch(PDOException $e) {
			if ($this->debug) {
				throw $e;
			} else {
				self::$qeuries[] = [
					$sql_after_render,
					$params
				];
				$errstr = $e->getMessage();
				$errfile = $e->getFile();
				$errline = $e->getLine();
				$errno = $e->getCode();
				KF::log("{$errstr} in {$errfile} on line {$errline} errno {$errno}\n{$sql_after_render}");
				return false;
			}
		}
		self::$qeuries[] = [
			$stmt->queryString,
			$params
		];

		if ($this->is_write_type($sql)) {
			return TRUE;
		}

		$record = new KF_DATABASE_record($stmt);
		if (count($this->ar_select) == 1) {
			if ($this->ar_select[0] != '*') {
				$record->column = $this->ar_select[0];
			}
		}
		return $record;
	}

	function quote($str) {
		if (!$this->pdo) {
			$this->_init();
		}
		return $this->pdo->quote($str);
	}

	function is_write_type($sql) {
		if (!preg_match('/^\s*"?(SET|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|TRUNCATE|LOAD DATA|COPY|ALTER|GRANT|REVOKE|LOCK|UNLOCK)\s/i', $sql)) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	public function lastInsertId() {
		if (!$this->pdo) return false;
		return $this->pdo->lastInsertId();
	}

	private function replacePrefix($sql, $prefix = '@__') {
		$sql = trim($sql);

		$escaped = false;
		$quoteChar = '';

		$n = strlen($sql);

		$startPos = 0;
		$literal = '';
		while ($startPos < $n) {
			$ip = strpos($sql, $prefix, $startPos);
			if ($ip === false) {
				break;
			}

			$j = strpos($sql, "'", $startPos);
			$k = strpos($sql, '"', $startPos);
			if (($k !== FALSE) && (($k < $j) || ($j === FALSE))) {
				$quoteChar = '"';
				$j = $k;
			} else {
				$quoteChar = "'";
			}

			if ($j === false) {
				$j = $n;
			}

			$literal .= str_replace($prefix, KF::getConfig()->table_prefix, substr($sql, $startPos, $j - $startPos));
			$startPos = $j;

			$j = $startPos + 1;

			if ($j >= $n) {
				break;
			}

			// quote comes first, find end of quote
			while (TRUE) {
				$k = strpos($sql, $quoteChar, $j);
				$escaped = false;
				if ($k === false) {
					break;
				}
				$l = $k - 1;
				while ($l >= 0 && $sql{$l} == '\\') {
					$l--;
					$escaped = !$escaped;
				}
				if ($escaped) {
					$j = $k + 1;
					continue;
				}
				break;
			}
			if ($k === FALSE) {

				// error in the query - no end quote; ignore it
				break;
			}
			$literal .= substr($sql, $startPos, $k - $startPos + 1);
			$startPos = $k + 1;
		}
		if ($startPos < $n) {
			$literal .= substr($sql, $startPos, $n - $startPos);
		}

		return $literal;
	}

	public function getQueries() {
		return self::$qeuries;
	}

	public function getErrorMsg() {
		$err_obj = $this->errorInfo();
		if (is_array($err_obj)) return $err_obj[2];
		return " ";
	}
	public function ping() {
		$pdoErrMode = self::getAttribute(PDO::ATTR_ERRMODE);
		self::setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$success = true;
		try {
			if ($this->getOne('SELECT 1') != 1) {
				$success = false;
			}
		}
		catch(Exception $e) {
			$success = false;
		}
		self::setAttribute(PDO::ATTR_ERRMODE, $pdoErrMode);
		return $success;
	}
}
?>
