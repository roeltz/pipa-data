<?php

namespace Pipa\Data\Util;
use Pipa\Data\SQLDataSource;

abstract class AbstractConvenientSQLDataSource implements SQLDataSource {

	function querySingle($sql, array $parameters = null) {
		$result = $this->query($sql, $parameters);
		return @$result[0];
	}

	function queryField($sql, array $parameters = null) {
		$result = $this->query($sql, $parameters);
		foreach($result as &$record) {
			$record = reset($record);
		}
		return $result;
	}

	function queryValue($sql, array $parameters = null) {
		$result = $this->querySingle($sql, $parameters);
		if ($result) {
			return current($result[0]);
		}
	}
}
