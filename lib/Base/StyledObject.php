<?php
/*
This file is part of Erben
A tool for making full e-books from pages digitized by Czech National Library
Copyright (C) 2014 Czech Pirate Party

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
namespace Base;

abstract class StyledObject {
	protected $data = array();

	protected function __construct(array $data, array $reqfields = array()) {
		foreach ($reqfields as $key) {
			if (!array_key_exists($key, $data)) {
				throw new \Exception(__CLASS__ . "(): Required field \"$key\" not set");
			}
		}

		$this->data = $data;
	}

	public function __get($name) {
		return isset($this->data[$name]) ? $this->data[$name] : null;
	}

	public function __isset($name) {
		return isset($this->data[$name]);
	}

	public function __toString() {
		return $this->render();
	}

	public function htmldata() {
		$ret = new \Web\HtmlData();

		foreach ($this->data as $key => $value) {
			$ret->$key = $value;
		}

		return $ret;
	}

	public function render($style = 'default') {
		$funcname = "style_$style";

		if (!method_exists($this, $funcname)) {
			throw new \Exception(__CLASS__ . "::render(): undefined style \"$style\"");
		}

		return $this->$funcname($this->htmldata());
	}

	protected function style_default(\Web\HtmlData $self) {
		return null;
	}
}
