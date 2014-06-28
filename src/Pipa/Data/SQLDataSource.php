<?php

namespace Pipa\Data;

interface SQLDataSource {
	function query($sql, array $parameters = null);
	function querySingle($sql, array $parameters = null);
	function queryField($sql, array $parameters = null);
	function queryValue($sql, array $parameters = null);
	function execute($sql, array $parameters = null);
}
