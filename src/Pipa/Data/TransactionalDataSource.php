<?php

namespace Pipa\Data;

interface TransactionalDataSource {
	function beginTransaction();
	function commit();
	function rollback();
}
