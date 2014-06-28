<?php

namespace Pipa\Data\Expression;
use Pipa\Data\Expression;

class JunctionExpression implements Expression {
	
	const OPERATOR_CONJUNCTION = "and";
	const OPERATOR_DISJUNCTION = "or";
	
	public $expressions;
	public $operator;
	
	function __construct($operator, array $expressions) {
		$this->operator = $operator;
		foreach($expressions as $expression) {
			$this->add($expression);
		}
	}
	
	function __clone() {
		foreach($this->expressions as &$expression) {
			$expression = clone $expression;
		}
	}
	
	function _and(Expression $expression) {
		if ($this->operator == self::OPERATOR_CONJUNCTION) {
			return $this->add($expression);
		} else {
			return new JunctionExpression(JunctionExpression::OPERATOR_CONJUNCTION, array($this, $expression));
		}
	}
	
	function _or(Expression $expression) {
		if ($this->operator == self::OPERATOR_DISJUNCTION) {
			return $this->add($expression);
		} else {
			return new JunctionExpression(JunctionExpression::OPERATOR_DISJUNCTION, array($this, $expression));
		}
	}
	
	function add(Expression $expression) {
		$this->expressions[] = $expression;
		return $this;
	}
}
