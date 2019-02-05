<?php

namespace Pipa\Data;

interface SQLCursorSupport {
	function querySQLCursor($sql, array $parameters = null);
}