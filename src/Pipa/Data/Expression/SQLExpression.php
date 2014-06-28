<?php

namespace Pipa\Data\Expression;
use Pipa\Data\Expression;

class SQLExpression implements Expression {
	
	public $sql;
	public $parameters;
	
	function __construct($sql, array $parameters = null) {
		$this->sql = $sql;
		$this->parameters = $parameters;
	}
}
