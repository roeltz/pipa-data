<?php

namespace Pipa\Data;

class Limit implements Criterion {

	public $length;
	public $offset;

	static function n($n) {
		return new self($n, 0);
	}

	static function page($page, $size) {
		return new self($size, ($page - 1) * $size);
	}

	static function single() {
		return new self(1, 0);
	}

	static function skip($offset) {
		return new self(-1, $offset);
	}

	static function slice($offset, $length) {
		return new self($length, $offset);
	}

	function __construct($length, $offset) {
		$this->length = $length;
		$this->offset = $offset;
	}
}
