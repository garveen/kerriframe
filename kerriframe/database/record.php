<?php
/**
* Class and Function List:
* Function list:
* - __construct()
* - one()
* - row()
* - result()
* - column()
* Classes list:
* - KF_DATABASE_record
*/

class KF_DATABASE_record
{
	protected $_result = null;
	protected $stmt = null;
	public $column = false;
	function __construct($stmt = false) {
		$this->stmt = $stmt;
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

	public function column($column = false) {
		if ($this->stmt === false) return false;
		if (!$column) {
			if (!$this->column) return false;
			$column = $this->column;
		}

		$ret = array();
		while (($result = $this->stmt->fetchObject())) {
			$ret[] = $result->$column;
		}
		return $ret;
	}
}
