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

class Template extends HtmlData {
	private $tplfile;

	public function __construct($tplname) {
		$file = APP_BASEDIR . '/data/templates/' . $tplname;

		if (!@file_exists($file)) {
			throw new \Exception("Template file \"$tplname\" not found");
		}

		$this->tplfile = $file;
	}

	public function render() {
		return renderTemplate($this->tplfile, $this);
	}
}
