<?php

namespace Pipa\Data;

interface CursorSupport {
	function queryCursor(Criteria $criteria);
}