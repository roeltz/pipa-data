<?php

namespace Pipa\Data;

class JoinableCollection extends Collection {
	
	public $joins = array();
	
	static function from($collection, $alias = null) {
		if (($collection instanceof Collection) && !($collection instanceof static)) {
			return new static($collection->name, $collection->alias);
		} else {
			return parent::from($collection, $alias);
		}
	}
	
	function __clone() {
		foreach($this->joins as &$join) {
			$join = clone $join;
		}
	}
	
	function join(Collection $collection, Expression $on, $type = Join::TYPE_INNER) {
		$this->joins[] = new Join($collection, $on, $type);
	}
	
	function leftJoin(Collection $collection, Expression $on) {
		$this->join($collection, $on, Join::TYPE_LEFT);
	}

	function rightJoin(Collection $collection, Expression $on) {
		$this->join($collection, $on, Join::TYPE_RIGHT);
	}
}	
