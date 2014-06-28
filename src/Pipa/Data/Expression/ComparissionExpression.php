<?php

namespace Pipa\Data\Expression;
use Pipa\Data\Expression;

class ComparissionExpression implements Expression {
	
	public $a;
	public $b;
	public $operator;
	
	function __construct($a, $b, $operator) {
		$this->a = $a;
		$this->b = $b;
		$this->operator = $operator;
	}
	
	function __clone() {
		if (is_object($this->b))
			$this->b = clone $this->b;
	}
	
	function _and(Expression $expression) {
		return new JunctionExpression(JunctionExpression::OPERATOR_CONJUNCTION, array($this, $expression));
	}
	
	function _or(Expression $expression) {
		return new JunctionExpression(JunctionExpression::OPERATOR_DISJUNCTION, array($this, $expression));
	}
}
