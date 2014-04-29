<?php
class KF_DATABASE_record
{
	protected $_result = null;
	protected $stmt = null;
	function __construct($stmt = false) {
		$this->stmt = $stmt;
	}
	function __call($name, $args) {
		if ($name == 'list') {
			return call_user_func_array([$this, 'result'] , $args);
		}
	}

	public function one() {

		if ($this->stmt === false) return false;

		$result = $this->stmt->fetchColumn(0);

		return $result;
	}

	public function row() {

		if ($this->stmt === false) return false;

		$result = $this->stmt->fetchObject();

		return $result;
	}

	public function result($key_column = null) {

		if ($this->stmt === false) return false;

		if ($key_column === null) {
			$result = $this->stmt->fetchAll(PDO::FETCH_OBJ);
			$this->stmt->closeCursor();

			return $result;
		} else {
			$ret = array();
			while (($result = $this->stmt->fetchObject())) {
				$ret[$result->$key_column] = $result;
			}
			return $ret;
		}
	}

	public function column($column) {
		if ($this->stmt === false) return false;

		$ret = array();
		while (($result = $this->stmt->fetchObject())) {
			$ret[] = $result->$column;
		}
		return $ret;

	}
}

class KF_DBO
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
		if ($options === null) {
			$options = array(
				PDO::ATTR_PERSISTENT => true
			);
		} else if (is_array($options)) {
			$options[PDO::ATTR_PERSISTENT] = true;
		}
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

		// 设置错误报告模式
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public function prepare($sql, $driver_options = array()) {
		if (!$this->pdo) {
			$this->_init();
		}
		$sql_after_render = $this->replacePrefix($sql);
		KF_DBO::$qeuries[] = $sql_after_render;
		return $this->pdo->prepare($sql_after_render, $driver_options);
	}

	public function query($sql, $params = array()) {
		$sql = ltrim($sql);

		if (!$this->pdo) {
			$this->_init();
		}

		if(is_object($params)) {
			$params = (array)$params;
		}

		if(!is_array($params)) {
			$params = [$params];
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
				KF_DBO::$qeuries[] = [
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
		KF_DBO::$qeuries[] = [
			$stmt->queryString,
			$params
		];
		$record = new KF_DATABASE_record($stmt);
		return $record;
	}

	public function lastInsertId() {
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
		return KF_DBO::$qeuries;
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
