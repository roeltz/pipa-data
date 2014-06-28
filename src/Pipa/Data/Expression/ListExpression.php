<?php

namespace Pipa\Data\Expression;
use Pipa\Data\Expression;
use Pipa\Data\Field;

class ListExpression implements Expression {

	const OPERATOR_IN = "in";
	const OPERATOR_NOT_IN = "not-in";

	public $field;
	public $operator;
	public $values;

	function __construct($operator, Field $field, array $values) {
		$this->field = $field;
		$this->operator = $operator;
		$this->values = $values;
	}
	
	function _and(Expression $expression) {
		return new JunctionExpression(JunctionExpression::OPERATOR_CONJUNCTION, array($this, $expression));
	}

	function _or(Expression $expression) {
		return new JunctionExpression(JunctionExpression::OPERATOR_DISJUNCTION, array($this, $expression));
	}
}
