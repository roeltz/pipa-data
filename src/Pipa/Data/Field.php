<?php

namespace Pipa\Data;

class Field {

	public $name;
	public $collection;

	static function from($field) {
		if (is_string($field)) {
			return new self($field);
		} elseif (is_array($field) && isset($field[0]) && isset($field[1])) {
			return new self($field[0], $field[1]);
		} else {
			return $field;
		}
	}

	function __construct($name, Collection $collection = null) {
		$this->name = $name;
		$this->collection = $collection;
	}
}
