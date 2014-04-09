<?php
class KF_Library_Xml_dom
{

	private $_dom;

	function __construct() {
		$this->_dom = new DOMDocument("1.0", "utf-8");
	}

	function addChild($parent_, $elem_name_) {
		$_root = $this->_dom->createElement($elem_name_);
		$parent_->appendChild($_root);
		return $_root;
	}

	function addAttribute($node_, $key_, $value_) {
		try {
			$_key = $this->_dom->createAttribute($key_);
			$node_->appendChild($_key);
			$_value_content = $this->_dom->createTextNode($value_);
			$_key->appendChild($_value_content);
		}
		catch(Exception $e) {
		}
	}

	function addCData($node_, $key_, $value_) {
		$_key = $this->_dom->createElement($key_);
		$node_->appendChild($_key);
		$_value_content = $this->_dom->createCDATASection($value_);
		$_key->appendChild($_value_content);
	}

	function appendContent($node_, $value_) {
		$node = $this->_dom->createTextNode($value_);
		$node_->appendChild($node);
	}

	function flush() {
		echo $this->getString();
	}

	function getString() {
		return $this->_dom->saveXML();
	}

	function __call($name, $args) {
		return call_user_func_array(array(
			$this->_dom,
			$name
		) , $args);
	}
}

class KF_Library_Xml
{

	/**
	 * convert Xml from data
	 * @param  mixed $data    Data to be converted
	 * @param  array  $tagDef tag defs
	 * @param  string $mode   enum('cdata', 'content', 'attr')
	 * @return string         xml
	 */
	function getXml($data, $tagDef = array() , $rootName = 'root', $mode = 'cdata') {
		$includeTags = isset($tagDef['includes']) ? $tagDef['includes'] : array();
		$excludeTags = isset($tagDef['excludes']) ? $tagDef['excludes'] : array();
		$cdataTags = isset($tagDef['cdatas']) ? $tagDef['cdatas'] : array();
		$this->dom = new KF_Library_Xml_dom;
		$root = $this->dom->addChild($this->dom, $rootName);
		$this->excludeTags = $excludeTags;
		$this->cdataTags = $cdataTags;
		$this->includeTags = $includeTags;
		$this->_parseToXml($this->dom, $root, $data, $mode);
		return $this->dom->getString();
	}

	function _parseToXml($dom, $node, $child, $mode) {
		foreach ($child as $k => $v) {
			if (!is_numeric($k) && (in_array($k, $this->excludeTags) || (!empty($this->includeTags) && !in_array($k, $this->includeTags)))) {
				continue;
			}
			if (is_array($v) || is_object($v)) {
				if (is_numeric($k)) {
					$cld = $this->dom->addChild($node, 'item');
				} else {
					$cld = $this->dom->addChild($node, $k);
				}

				$this->_parseToXml($dom, $cld, $v, $mode);
			} else {
				if (is_numeric($k)) {
					$k = 'item';
				}
				if ($mode == 'cdata' || in_array($k, $this->cdataTags)) {
					$this->dom->addCData($node, $k, $v);
				} elseif ($mode == 'content') {
					$this->dom->appendContent($node, $k, $v);
				} else {
					$this->dom->addAttribute($node, $k, $v);
				}
			}
		}
	}
}

