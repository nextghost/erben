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

function p($format) {
	$args = func_get_args();

	foreach ($args as $item) {
		if (is_null($item)) {
			return null;
		}
	}

	return vsprintf($format, array_slice($args, 1));
}

/**
 * Translate string into user's language
 * Yes, this is a primitive reimplementation of gettext(). Unfortunately,
 * using gettext() with per-user language settings on multithreaded server
 * will lead to lots of fun with race conditions. The only way to set language
 * for gettext() is through putenv() or setlocale()...
 */
function tr($msg, $num = null) {
	global $lang;

	return $lang->translate($msg, $num);
}

function initlang() {
	global $lang;
	$cfg = new \Common\Config();
	$conf = $cfg->config();
	$deflang = null;

	if (isset($conf['sys']['default_lang'])) {
		$deflang = $conf['sys']['default_lang'];
	}

	$lang = new \Common\Translator($deflang);
}
