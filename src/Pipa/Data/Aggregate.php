<?php

namespace Pipa\Data;

class Aggregate {
	
	const OPERATION_AVG = "avg";
	const OPERATION_MAX = "max";
	const OPERATION_MIN = "min";
	const OPERATION_SUM = "sum";
	
	public $field;
	public $operation;
		
	static function avg($field) {
		return new self(self::OPERATION_AVG, Field::from($field));
	}

	static function max($field) {
		return new self(self::OPERATION_MAX, Field::from($field));
	}

	static function min($field) {
		return new self(self::OPERATION_MIN, Field::from($field));
	}

	static function sum($field) {
		return new self(self::OPERATION_SUM, Field::from($field));
	}
	
	function __construct($operation, Field $field) {		
		$this->field = $field;
		$this->operation = $operation;
	}
}
