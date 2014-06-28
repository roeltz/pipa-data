<?php

namespace Pipa\Data;

interface MultipleInsertionSupport {
	function saveMultiple(array $values, Collection $collection);
}
