<?php

namespace Pipa\Data;
use Pipa\Registry\Registry;

abstract class ConnectionManager {
	
	/**
	 * @return DataSource
	 */
	static function get($name = "default") {
		return Registry::getByClass(get_called_class(), "datasources", $name);
	}
	
	static function set($name, $callable) {
		Registry::setSingletonByClass(get_called_class(), "datasources", $name, $callable);
	}
}
