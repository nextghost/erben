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
namespace Common;


class Translator {
	private $lang = null;
	private $msgdata = array();
	private $plfunc = '\Common\Translator::plural';

	public static function plural($num) {
		return $num == 1 ? 0 : 1;
	}

	public function __construct($lang) {
		if (is_null($lang) || !preg_match('/^[a-z_-]+$/i', $lang)) {
			return;
		}

		$path = APP_BASEDIR . "/data/lang/$lang.php";

		if (!@include $path) {
			return;
		}

		$this->lang = $lang;

		if (isset($messages)) {
			$this->msgdata = $messages;
		}

		$plfunc = "\\Lang\\$lang\\plural";

		if (function_exists($plfunc)) {
			$this->plfunc = $plfunc;
		}
	}

	public function translate($msg, $num = null) {
		if (is_null($num)) {
			if (isset($this->msgdata[$msg])) {
				return $this->msgdata[$msg];
			}
		} else {
			$form = call_user_func($this->plfunc, $num);

			if (isset($this->msgdata[$msg][$form])) {
				return $this->msgdata[$msg][$form];
			}
		}

		return $msg;
	}

	public function isValid() {
		return !is_null($this->lang);
	}
}
