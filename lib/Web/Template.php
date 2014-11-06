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

/**
 * Block template's access to private properties of Template class
 */
function renderTemplate($templatefilename, Template $self) {
	try {
		ob_start();
		require $templatefilename;
		return ob_get_clean();
	} catch (\Exception $e) {
		ob_end_clean();
		throw $e;
	}
}

class Template {
	private $tplfile;
	private $data = array();

	public function __construct($tplname) {
		$file = APP_BASEDIR . '/data/templates/' . $tplname;

		if (!@file_exists($file)) {
			throw new \Exception("Template file \"$tplname\" not found");
		}

		$this->tplfile = $file;
	}

	public function __get($name) {
		return isset($this->data[$name]) ? $this->data[$name] : '';
	}

	/**
	 * If the attribute name starts with underscore, it will be rendered
	 * without any changes. Anything else will be escaped using
	 * htmlspecialchars().
	 */
	public function __set($name, $value) {
		if (substr($name, 0, 1) != '_') {
			$value = htmlspecialchars($value);
		}

		$this->data[$name] = $value;
	}

	public function __isset($name) {
		return isset($this->data[$name]);
	}

	public function __unset($name) {
		unset($this->data[$name]);
	}

	public function render() {
		return renderTemplate($this->tplfile, $this);
	}
}
