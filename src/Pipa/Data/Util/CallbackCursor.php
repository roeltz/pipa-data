<?php

namespace Pipa\Data\Util;
use Pipa\Data\Cursor;
use stdClass;

class CallbackCursor implements Cursor {

	protected $rewindCallback;

	protected $nextCallback;

	protected $current;

	protected $index;

	protected $eof;

	function __construct(callable $next, callable $rewind = null) {
		$this->nextCallback = $next;
		$this->rewindCallback = $rewind;
		$this->eof = new stdClass;
	}

	function current() {
		return $this->current;
	}

	function key() {
		return $this->index;
	}

	function next() {
		$this->index++;
		$callback = $this->nextCallback;
		$this->current = $callback($this->eof);
	}

	function rewind() {
		$this->index = 0;
		if ($this->rewindCallback) {
			$callback = $this->rewindCallback;
			$callback();
		}
		$this->next();
	}

	function valid() {
		return $this->current !== $this->eof;
	}
}