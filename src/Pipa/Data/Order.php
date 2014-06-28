<?php

namespace Pipa\Data;

class Order implements Criterion {

	const TYPE_ASC = "asc";
	const TYPE_DESC = "desc";

	public $field;
	public $type;

	static function asc($field) {
		return new self($field, self::TYPE_ASC);
	}

	static function desc($field) {
		return new self($field, self::TYPE_DESC);
	}

	function __construct($field, $type) {
		$this->field = Field::from($field);
		$this->type = $type;
	}
	
	function __clone() {
		$this->field = clone $this->field;
	}
}
