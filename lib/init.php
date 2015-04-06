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

function autoload($class) {
	$tokens = explode('\\', $class);
	$path = APP_BASEDIR . '/lib/' . str_replace('\\', '/', $class) . '.php';

	if (@file_exists($path)) {
		require_once $path;
	}
}

function error_handler($errno, $errstr, $file, $line) {
	throw new \ErrorException($errstr, $errno, 1, $file, $line);
}

function init_timezone() {
	$cfg = new \Common\Config();
	$conf = $cfg->config();

	if (empty($conf['sys']['default_timezone']) || !@date_default_timezone_set($conf['sys']['default_timezone'])) {
		$tz = ini_get('date.timezone');

		if (empty($tz)) {
			date_default_timezone_set('UTC');
		}
	}
}

spl_autoload_register('\\Common\\autoload');
set_error_handler('\\Common\\error_handler');
init_timezone();
require_once APP_BASEDIR . '/lib/tpltools.php';
initlang();
