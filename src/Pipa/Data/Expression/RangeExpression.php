<?php

namespace Pipa\Data\Expression;
use Pipa\Data\Expression;
use Pipa\Data\Field;

class RangeExpression implements Expression {
	
	public $field;
	public $max;
	public $min;
	
	function __construct(Field $field, $min, $max) {
		$this->field = $field;
		$this->max = max(array($max, $min));
		$this->min = min(array($max, $min));
	}
	
	function __clone() {
		$this->field = clone $this->field;
	}

	function _and(Expression $expression) {
		return new JunctionExpression(JunctionExpression::OPERATOR_CONJUNCTION, array($this, $expression));
	}
	
	function _or(Expression $expression) {
		return new JunctionExpression(JunctionExpression::OPERATOR_DISJUNCTION, array($this, $expression));
	}
}