<?php

namespace Pipa\Data;

class Collection {
		
	public $name;
	public $alias;
	
	static function from($collection, $alias = null) {
		if (is_string($collection)) {
			return new static($collection, $alias);
		} elseif (is_array($collection)) {
			return new static($collection[0], $collection[1]);
		} elseif ($collection instanceof self) {
			return $collection;
		}
	}
	
	final function __construct($name, $alias = null) {
		$this->name = $name;
		$this->alias = $alias;
	}
	
	function field($name) {
		return new Field($name, $this);
	}
}
