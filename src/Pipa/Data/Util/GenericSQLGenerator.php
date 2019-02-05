<?php

namespace Pipa\Data\Util;
use Pipa\Data\Aggregate;
use Pipa\Data\Collection;
use Pipa\Data\Criteria;
use Pipa\Data\Expression;
use Pipa\Data\Expression\ComparissionExpression;
use Pipa\Data\Expression\JunctionExpression;
use Pipa\Data\Expression\ListExpression;
use Pipa\Data\Expression\NegationExpression;
use Pipa\Data\Expression\RangeExpression;
use Pipa\Data\Expression\SQLExpression;
use Pipa\Data\Field;
use Pipa\Data\JoinableCollection;
use Pipa\Data\Limit;
use Pipa\Data\Order;
use Pipa\Data\Restrictions;

abstract class GenericSQLGenerator {

	abstract function escapeField(Field $field);
	abstract function escapeIdentifier($name);
	abstract function escapeValue($value);

	function generateAggregate(Aggregate $aggregate, Criteria $criteria) {
		return join(' ', $this->generateAggregateComponents($aggregate, $criteria));
	}

	function generateAggregateComponents(Aggregate $aggregate, Criteria $criteria) {
		$components = $this->generateSelectComponents($criteria);
		$components['fields'] = strtoupper($aggregate->operation).'('.$this->escapeField($aggregate->field).')';
		return $components;
	}

	function generateCount(Criteria $criteria) {
		return join(' ', $this->generateCountComponents($criteria));
	}

	function generateCountComponents(Criteria $criteria) {
		$components = $this->generateSelectComponents($criteria);
		$components['fields'] = 'COUNT(*)';
		return $components;
	}

	function generateDelete(Criteria $criteria) {
		return join(' ', $this->generateDeleteComponents($criteria));
	}

	function generateDeleteComponents(Criteria $criteria) {
		$components = array('keyword'=>'DELETE FROM');

		$components['collection'] = $this->renderCollection($criteria->collection);

		if ($criteria->expressions) {
			$components['where'] = 'WHERE '.$this->renderExpressions($criteria->expressions);
		}

		return $components;
	}

	function generateInsert(array $values, Collection $collection) {
		return join(' ', $this->generateInsertComponents($values, $collection));
	}

	function generateMultipleInsert(array $values, Collection $collection) {
		return join(' ', $this->generateMultipleInsertComponents($values, $collection));
	}

	function generateInsertComponents(array $values, Collection $collection) {
		$components = $this->generateInsertHeaderComponents(array_keys($values), $collection);
		$components['values'] = 'VALUES '.$this->generateInsertValues($values);
		return $components;
	}

	function generateMultipleInsertComponents(array $values, Collection $collection) {
		$components = $this->generateInsertHeaderComponents(array_keys($values[0]), $collection);

		$valuesList = array();
		foreach($values as $row) {
			$valuesList[] = $this->generateInsertValues($row);
		}
		$components['values'] = 'VALUES '.join(', ', $valuesList);

		return $components;
	}

	function generateInsertHeaderComponents(array $fields, Collection $collection) {
		$components = array('keyword'=>'INSERT INTO');

		$components['collection'] = $this->renderCollection($collection);

		$escapedFields = array();
		foreach($fields as $field) {
			$escapedFields[] = $this->escapeField(Field::from($field));
		}
		$components['fields'] = '('.join(', ', $escapedFields).')';

		return $components;
	}

	function generateInsertValues(array $values) {
		$escapedValues = array();
		foreach($values as $value) {
			$escapedValues[] = $this->escapeValue($value);
		}
		return '('.join(', ', $escapedValues).')';
	}

	function generateSelect(Criteria $criteria, array $fields = null) {
		return join(' ', $this->generateSelectComponents($criteria, $fields));
	}

	function generateSelectComponents(Criteria $criteria, array $fields = null) {
		$self = $this;
		$components = array('keyword'=>'SELECT');

		if ($criteria->fields) {
			if ($criteria->distinct) {
				$components['distinct'] = 'DISTINCT';
			}
			$components['fields'] = join(', ', array_map(array($this, 'escapeField'), $criteria->fields));
		} else {
			$components['fields'] = '*';
		}

		$components['collection'] = 'FROM '.$this->renderCollection($criteria->collection);

		if ($criteria->expressions) {
			if ($where = trim($this->renderExpressions($criteria->expressions), '()')) {
				$components['where'] = 'WHERE '.$this->renderExpressions($criteria->expressions);
			}
		}

		if ($criteria->order) {
			$components['order-by'] = $this->renderOrder($criteria->order);
		}

		if ($criteria->limit) {
			$components['limit'] = $this->renderLimit($criteria->limit);
		}

		return $components;
	}

	function generateUpdate(array $values, Criteria $criteria) {
		return join(' ', $this->generateUpdateComponents($values, $criteria));
	}

	function generateUpdateComponents(array $values, Criteria $criteria) {
		$components = array('keyword'=>'UPDATE');

		$components['collection'] = $this->renderCollection($criteria->collection);

		$vars = array();
		foreach($values as $field=>$value) {
			$vars[] = $this->escapeField(Field::from($field)).' = '.$this->escapeValue($value);
		}
		$components['set'] = 'SET '.join(', ', $vars);

		if ($criteria->expressions) {
			$components['where'] = 'WHERE '.$this->renderExpressions($criteria->expressions);
		}

		return $components;
	}

	function interpolateParameters($sql, array $parameters) {
		$self = $this;
		return \Pipa\interpolate($sql, function($key) use($self, $parameters){
			return $self->escapeValue(@$parameters[$key]);
		});
	}

	function renderCollection(Collection $collection) {
		$rendered = $this->escapeIdentifier($collection->name);

		if ($collection->alias) {
			$rendered .= ' AS '.$this->escapeIdentifier($collection->alias);
		}

		if ($collection instanceof JoinableCollection && $collection->joins) {
			foreach($collection->joins as $join) {
				$rendered .= ' '.strtoupper($join->type).' JOIN '.$this->renderCollection($join->collection).' ON '.$this->renderExpression($join->expression);
			}
		}

		return $rendered;
	}

	function renderComparissionExpression(ComparissionExpression $expression) {
		$a = $this->escapeField($expression->a);
		$o = $expression->operator;
		$b = $expression->b instanceof Field ? $this->escapeField($expression->b) : $this->escapeValue($expression->b);
		if ($o == '=' && $b === 'NULL') {
			return "$a IS NULL";
		} elseif ($o == '<>' && $b === 'NULL') {
			return "$a IS NOT NULL";
		} elseif ($o == 'like') {
			return $this->renderLike($a, $b);
		} elseif ($o == 'regex') {
			return $this->renderRegex($a, $b);
		} else {
			return "$a $o $b";
		}
	}

	function renderExpression(Expression $expression) {
		if ($expression instanceof ComparissionExpression) {
			return $this->renderComparissionExpression($expression);
		} elseif ($expression instanceof ListExpression) {
			return $this->renderListExpression($expression);
		} elseif ($expression instanceof RangeExpression) {
			return $this->renderRangeExpression($expression);
		} elseif ($expression instanceof JunctionExpression) {
			return $this->renderJunctionExpression($expression);
		} elseif ($expression instanceof NegationExpression) {
			return $this->renderNegationExpression($expression);
		} elseif ($expression instanceof SQLExpression) {
			return $this->renderSQLExpression($expression);
		}
	}

	function renderExpressions(array $expressions) {
		if (count($expressions) == 1 && $expressions[0] instanceof JunctionExpression) {
			$expressions = $expressions[0];
		} else {
			$expressions = Restrictions::_and($expressions);
		}
		return $this->renderExpression($expressions);
	}

	function renderJunctionExpression(JunctionExpression $expression) {
		$rendered = array();
		foreach($expression->expressions as $e) {
			if ($e = $this->renderExpression($e)) {
				$rendered[] = $e;
			}
		}
		return '('.join(strtoupper(") {$expression->operator} ("), $rendered).')';
	}

	function renderLike($a, $b) {
		return "$a LIKE $b";
	}

	function renderListExpression(ListExpression $expression) {
		if ($expression->values) {
			$values = array();
			foreach($expression->values as $v) {
				$values[] = $this->escapeValue($v);
			}
			$values = join(', ', $values);
			if ($values) {
				$field = $this->escapeField($expression->field);
				switch($expression->operator) {
					case ListExpression::OPERATOR_IN:
						return "$field IN ($values)";
					case ListExpression::OPERATOR_NOT_IN:
						return "$field NOT IN ($values)";
				}
			}
		}
	}

	function renderLimit(Limit $limit) {
		if ($limit->length == -1) {
			return "OFFSET {$limit->offset}";
		} else {
			return "LIMIT {$limit->length} OFFSET {$limit->offset}";
		}
	}

	function renderNegationExpression(NegationExpression $expression) {
		return 'NOT ('.$this->renderExpression($expression).')';
	}

	function renderOrder(array $order) {
		$rendered = array();
		foreach($order as $o) {
			switch($o->type) {
				case Order::TYPE_ASC:
				case Order::TYPE_DESC:
					$rendered[] = $this->escapeField($o->field).' '.strtoupper($o->type);
					break;
				case Order::TYPE_RANDOM:
					$rendered[] = $this->getRandomOrderExpression();
					break;
				default:
					continue 2;
			}
		}
		return 'ORDER BY '.join(', ', $rendered);
	}

	function renderRangeExpression(RangeExpression $expression) {
		$field = $this->escapeField($expression->field);
		$min = $this->escapeValue($expression->min);
		$max = $this->escapeValue($expression->max);
		return "$field BETWEEN $min AND $max";
	}

	function renderRegex($a, $b) {
		return "$a SIMILAR TO $b";
	}

	function renderSQLExpression(SQLExpression $expression) {
		if ($expression->parameters) {
			return \Pipa\fill($expression->sql, $expression->parameters);
		} else {
			return $expression->sql;
		}
	}
}
