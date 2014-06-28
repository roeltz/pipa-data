<?php

namespace Pipa\Data;

class Join {

	const TYPE_INNER = "inner";
	const TYPE_LEFT = "left";
	const TYPE_RIGHT = "right";
	
	public $collection;
	public $expression;
	public $type;
	
	function __construct(Collection $collection, Expression $expression, $type = self::TYPE_INNER) {
		$this->collection = $collection;
		$this->expression = $expression;
		$this->type = $type;
	}
	
	function __clone() {
		$this->expression = clone $this->expression;
	}
}
