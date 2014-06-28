<?php

namespace Pipa\Data;

/**
 * Provides a way to add functionality on top of a DataSource-specific
 * Criteria implementation without losing low-level functionality
 */
class CriteriaDecorator extends Criteria {

	protected $criteria;
	protected static $passthough = array(
		'collection', 'dataSource', 'distinct', 'expressions',
		'fields', 'index', 'limit', 'order'
	);

	function __construct(Criteria $criteria) {
		$this->criteria = $criteria;
		foreach(self::$passthough as $property) {
			unset($this->$property);
		}
	}
	
	function __clone() {
		$this->criteria = clone $this->criteria;
	}

	function __get($property) {
		if (in_array($property, self::$passthough)) {
			return $this->criteria->$property;
		}
	}

	function __set($property, $value) {
		if (in_array($property, self::$passthough)) {
			$this->criteria->$property = $value;
		} else {
			$this->$property = $value;
		}
	}

	function getCriteria() {
		return $this->criteria;
	}

	function add(Criterion $criterion) {
		$this->criteria->add($criterion);
		return $this;
	}

	function addAll($_ = null) {
		foreach(\Pipa\array_flatten(func_get_args()) as $arg)
			$this->add($arg);
		return $this;
	}

	function aggregate(Aggregate $aggregate) {
		return $this->criteria->aggregate($aggregate);
	}

	function count() {
		return $this->criteria->count();
	}

	function delete() {
		return $this->criteria->delete();
	}

	function distinct($fields) {
		$this->criteria->distinct($fields);
		return $this;
	}

	function eq($a, $b) {
		$this->criteria->eq($a, $b);
		return $this;
	}

	function fields($fields) {
		$this->criteria->fields($fields);
		return $this;
	}

	function from($collection) {
		$this->criteria->from($collection);
		return $this;
	}
	
	function indexBy($field) {
		$this->criteria->indexBy($field);
		return $this;
	}

	function limit(Limit $limit) {
		$this->criteria->limit($limit);
		return $this;
	}

	function order(Order $order) {
		$this->criteria->order($order);
		return $this;
	}

	function orderBy($field, $type = Order::TYPE_ASC) {
		$this->criteria->orderBy($field, $type);
		return $this;
	}

	function page($page, $size) {
		$this->criteria->page($page, $size);
		return $this;
	}
	
	function pageCount($size) {
		return $this->criteria->pageCount($size);
	}

	function n($n) {
		$this->criteria->n($n);
		return $this;
	}

	function queryAll() {
		return $this->criteria->queryAll();
	}

	function queryField($field = null) {
		return $this->criteria->queryField($field);
	}

	function querySingle() {
		return $this->criteria->querySingle();
	}

	function queryValue() {
		return $this->criteria->queryValue();
	}

	function update(array $values) {
		return $this->criteria->update($values);
	}

	function where(Expression $expression) {
		$this->criteria->where($expression);
		return $this;
	}
}
