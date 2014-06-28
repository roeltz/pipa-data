<?php

namespace Pipa\Data;

class RelationalCriteria extends Criteria {
	
	function from($collection) {
		$this->collection = JoinableCollection::from($collection);
		return $this;
	}
	
	function join($collection, Expression $on) {
		$this->collection->join(Collection::from($collection), $on);
		return $this;
	}
}
