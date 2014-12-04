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
namespace Web;

class HtmlData {
	protected $data = array();
	protected $cache = array();
	protected $gluestr = '';

	/**
	 * Set glue string for rendering array values
	 * Return $this for call chaining
	 */
	public function glue($str) {
		$this->gluestr = $str;
		return $this;
	}

	protected function renderItem($item, $escape) {
		if (is_array($item)) {
			$ret = '';

			foreach ($item as $val) {
				if (!empty($ret)) {
					$ret .= $this->gluestr;
				}

				$ret .= $this->renderItem($val, $escape);
			}

			return $ret;
		} else if (is_object($item)) {
			return $item->render();
		} else if ($escape) {
			return htmlspecialchars($item);
		}

		return $item;
	}

	/**
	 * If the attribute name starts with underscore, it will be rendered
	 * without any changes. Anything else will be escaped using
	 * htmlspecialchars(). Arrays will be recursively folded with unescaped
	 * glue string inserted between each (escaped) item. Objects will be
	 * render()ed and the result returned without escaping.
	 */
	public function __get($name) {
		$escape = substr($name, 0, 1) != '_';

		if (!$escape) {
			$name = substr($name, 1);
		} else if (isset($this->cache[$name])) {
			return $this->cache[$name];
		}

		if (!isset($this->data[$name])) {
			return null;
		}

		$ret = $this->renderItem($this->data[$name], $escape);

		if ($escape && is_scalar($this->data[$name])) {
			$this->cache[$name] = $ret;
		}

		return $ret;
	}

	/**
	 * Don't use property names with leading underscores in the following
	 * methods. __get() uses leading underscore as special flag and its
	 * property name lookup may become erratic.
	 */
	public function __set($name, $value) {
		$this->data[$name] = $value;
		unset($this->cache[$name]);
	}

	public function __isset($name) {
		return isset($this->data[$name]);
	}

	public function __unset($name) {
		unset($this->data[$name]);
	}
}
