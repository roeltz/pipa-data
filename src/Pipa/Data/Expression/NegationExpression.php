<?php

namespace Pipa\Data\Expression;
use Pipa\Data\Expression;

class NegationExpression implements Expression {
	
	public $expression;
	
	function __construct(Expression $expression) {
		$this->expression = $expression;
	}
	
	function __clone() {
		$this->expression = clone $this->expression;
	}

	function _and(Expression $expression) {
		return new JunctionExpression(JunctionExpression::OPERATOR_CONJUNCTION, array($this, $expression));
	}
	
	function _or(Expression $expression) {
		return new JunctionExpression(JunctionExpression::OPERATOR_DISJUNCTION, array($this, $expression));
	}
}
