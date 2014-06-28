<?php

namespace Pipa\Data;
use Pipa\Data\Expression\ComparissionExpression;
use Pipa\Data\Expression\JunctionExpression;
use Pipa\Data\Expression\ListExpression;
use Pipa\Data\Expression\NegationExpression;
use Pipa\Data\Expression\RangeExpression;
use Pipa\Data\Expression\SQLExpression;

abstract class Restrictions {
	
	static function createAlias($alias = 'R') {
		class_alias(__CLASS__, $alias);
	}

	static function _and($_) {
		return new JunctionExpression(JunctionExpression::OPERATOR_CONJUNCTION, is_array($_) ? $_ : func_get_args());
	}

	static function _not(Expression $e) {
		return new NegationExpression($e);
	}

	static function _or($_) {
		return new JunctionExpression(JunctionExpression::OPERATOR_DISJUNCTION, is_array($_) ? $_ : func_get_args());
	}

	static function asc($field) {
		return Order::asc($field);
	}

	static function desc($field) {
		return Order::desc($field);
	}

	static function eq($a, $b) {
		return new ComparissionExpression(Field::from($a), $b, '=');
	}

	static function eqAll(array $values) {
		$expressions = array();
		foreach($values as $field=>$value) {
			$expressions[] = self::eq($field, $value);
		}
		return self::_and($expressions);
	}

	static function eqf($a, $b) {
		return new ComparissionExpression(Field::from($a), Field::from($b), '=');
	}

	static function ge($a, $b) {
		return new ComparissionExpression(Field::from($a), $b, '>=');
	}

	static function gef($a, $b) {
		return new ComparissionExpression(Field::from($a), Field::from($b), '>=');
	}

	static function gt($a, $b) {
		return new ComparissionExpression(Field::from($a), $b, '>');
	}

	static function gtf($a, $b) {
		return new ComparissionExpression(Field::from($a), Field::from($b), '>');
	}

	static function in($field, $_) {
		return new ListExpression(ListExpression::OPERATOR_IN, Field::from($field), is_array($_) ? $_ : array_slice(func_get_args(), 1));
	}

	static function le($a, $b) {
		return new ComparissionExpression(Field::from($a), $b, '<=');
	}

	static function lef($a, $b) {
		return new ComparissionExpression(Field::from($a), Field::from($b), '<=');
	}
	
	static function between($field, $a, $b) {
		return new RangeExpression(Field::from($field), $a, $b);
	}

	static function like($field, $pattern) {
		return new ComparissionExpression(Field::from($field), $pattern, 'like');
	}
	
	static function likeWords($field, $words, $splitPattern = '/\s+/') {
		if (is_string($words))
			$words = array_filter(preg_split($splitPattern, $words));
		$expressions = array();
		$fields = (array) $field;
		foreach($fields as $f) {
			foreach($words as $w) {
				$expressions[] = self::like($f, "%$w%");
			}
		}
		return self::_or($expressions);
	}

	static function lt($a, $b) {
		return new ComparissionExpression(Field::from($a), $b, '<');
	}

	static function ltf($a, $b) {
		return new ComparissionExpression(Field::from($a), Field::from($b), '<');
	}

	static function n($n) {
		return Limit::n($n);
	}

	static function ne($a, $b) {
		return new ComparissionExpression(Field::from($a), $b, '<>');
	}

	static function nef($a, $b) {
		return new ComparissionExpression(Field::from($a), Field::from($b), '<>');
	}

	static function nin($field, $_) {
		return new ListExpression(ListExpression::OPERATOR_NOT_IN, Field::from($field), is_array($_) ? $_ : array_slice(func_get_args(), 1));
	}

	static function not(Expression $expression) {
		return new NegationExpression($expression);
	}

	static function page($page, $size) {
		return Limit::page($page, $size);
	}

	static function regex($field, $pattern) {
		return new ComparissionExpression(Field::from($field), $pattern, 'regex');
	}

	static function single() {
		return Limit::single();
	}

	static function skip($offset) {
		return Limit::skip($offset);
	}

	static function slice($offset, $length = -1) {
		return Limit::slice($offset, $length);
	}

	static function sql($sql, array $parameters = null) {
		return new SQLExpression($sql, $parameters);
	}
}
