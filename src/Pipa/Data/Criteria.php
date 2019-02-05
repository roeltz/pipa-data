<?php

namespace Pipa\Data;

use Pipa\Data\Exception\CriteriaException;
use Pipa\Data\Exception\UnsupportedFeatureException;

/**
 * Represents the constraints, sorting and other parameters of a data query
 */
class Criteria {

	public $collection;
	public $dataSource;
	public $distinct = false;
	public $expressions = array();
	public $fields = array();
	public $index;
	public $limit;
	public $order = array();

	function __construct(DataSource $dataSource) {
		$this->dataSource = $dataSource;
	}

	function __clone() {
		foreach($this->expressions as &$expression) {
			$expression = clone $expression;
		}
	}

	function add(Criterion $criterion) {
		if ($criterion instanceof Limit)
			$this->limit($criterion);
		elseif ($criterion instanceof Order)
			$this->order($criterion);
		else
			$this->where($criterion);
		return $this;
	}

	function addAll($_ = null) {
		foreach(\Pipa\array_flatten(func_get_args()) as $arg)
			$this->add($arg);
		return $this;
	}

	/**
	 * Returns the computation of an aggregate operation
	 */
	function aggregate(Aggregate $aggregate) {
		return $this->dataSource->aggregate($aggregate, $this);
	}

	/**
	 * Returns number of elements matching the criteria
	 */
	function count() {
		return $this->dataSource->count($this);
	}

	/**
	 * Deletes the elements matching the criteria
	 */
	function delete() {
		return $this->dataSource->delete($this);
	}

	/**
	 * Sets the fields whose values must be returned uniquely
	 *
	 * @param (string|Field)[] $fields
	 */
	function distinct($fields) {
		if (!is_array($fields)) $fields = func_get_args();
		$this->fields($fields);
		$this->distinct = true;
		return $this;
	}

	function eq($a, $b) {
		$this->where(Restrictions::eq($a, $b));
		return $this;
	}

	/**
	 * Sets the fields to be used for the result set
	 *
	 * @param (string|Field)[] $fields
	 */
	function fields($fields) {
		if (!is_array($fields)) $fields = func_get_args();
		$this->fields = array();
		foreach($fields as $field) {
			$this->fields[] = Field::from($field);
		}
		return $this;
	}

	/**
	 * Sets the collection where the query should match the criteria
	 */
	function from($collection) {
		$this->collection = Collection::from($collection);
		return $this;
	}

	function indexBy($field) {
		$this->index = $field;
		return $this;
	}

	/**
	 * Sets the limiting parameters for the result set
	 *
	 * @param Limit $limit A Limit object
	 * @see Limit
	 * @see Restrict
	 */
	function limit(Limit $limit) {
		$this->limit = $limit;
		return $this;
	}

	function order(Order $order) {
		$this->order[] = $order;
		return $this;
	}

	/**
	 * Adds a field to be sorted for the result set
	 *
	 * @param Order $order An Order object
	 * @see Order
	 * @see Restrict
	 */
	function orderBy($field, $type = Order::TYPE_ASC) {
		$this->order(new Order($field, $type));
		return $this;
	}

	/**
	 * Shortcut method to set the limits according to paging parameters
	 */
	function page($page, $size) {
		$this->limit(Limit::page($page, $size));
		return $this;
	}

	/**
	 * Get result page count
	 * @param int $size The size of the page
	 */
	function pageCount($size) {
		return ceil($this->count() / ($size > 0 ? $size : 1));
	}

	/**
	 * Shortcut method to set the limits according to first N results
	 */
	function n($n) {
		$this->limit(Limit::n($n));
		return $this;
	}

	/**
	 * Sends a query to the DataSource and retrieves all the results
	 */
	function queryAll() {
		if ($this->collection) {
			$result = $this->dataSource->find($this);
			if ($this->index)
				$result = $this->indexResult($this->index, $result);
			return $result;
		} else {
			throw new CriteriaException("Collection not supplied");
		}
	}

	function queryCursor() {
		if ($this->dataSource instanceof CursorSupport) {
			return $this->dataSource->queryCursor($this);
		} else {
			throw new UnsupportedFeatureException("Cursor not supported by data source " . get_class($this->dataSource));
		}
	}

	/**
	 * Sends a query to the DataSource and retrieves all the results for a single
	 * field of the collection
	 *
	 * @param (string|Field) $field The field to be retrieved. If not field is specified, the first field of the result set is used.
	 */
	function queryField($field = null) {
		if ($field) $this->fields($field);
		$result = $this->queryAll();
		$items = array();
		foreach($result as $item) {
			reset($item);
			$items[] = current($item);
		}
		return $items;
	}

	/**
	 * Sends a query to the DataSource and retrieves a single record
	 */
	function querySingle() {
		$this->limit = Limit::single();
		if ($result = $this->queryAll()) {
			reset($result);
			return current($result);
		}
	}

	/**
	 * Sends a query to the DataSource and retrieves a single value
	 */
	function queryValue() {
		if ($result = $this->querySingle()) {
			reset($result);
			return current($result);
		}
	}

	/**
	 * Updates the collection records matching the criteria with the given values
	 *
	 * @param array $values
	 */
	function update(array $values) {
		return $this->dataSource->update($values, $this);
	}

	/**
	 * Adds a expression to the criteria
	 *
	 * Note that all expressions added to the criteria are joined into a single
	 * conjunction (AND operator) when the query is sent. If your criteria consists
	 * of a single disjunction (OR operator), you must build it before adding it.
	 *
	 * @see Restrictions
	 */
	function where(Expression $expression) {
		$this->expressions[] = $expression;
		return $this;
	}

	protected function indexResult($field, array $result) {
		$indexed = array();
		foreach($result as $record) {
			$indexed[$this->computeRecordIndex($this->index, $record)] = $record;
		}
		return $indexed;
	}

	protected function computeRecordIndex($field, $record) {
		$value = @$record[$field];
		if (is_scalar($value)) {
			return $value;
		} elseif ($value instanceof DateTime) {
			return $value->format("Y-m-d H:i:s.u");
		}
	}
}
