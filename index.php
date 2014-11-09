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

define('APP_BASEDIR', dirname(__FILE__));
require 'lib/init.php';

try {
	$path = \Base\Page::pageUrl();
	$page = $path->getChunk(0);
	$page = empty($page) ? 'index' : $page;
} catch (Exception $e) {
	die($e->getMessage());
}

try {
	if (!preg_match('/^[a-z][a-z0-9]*$/i', $page)) {
		\Base\Page::errorNotFound();
	}

	$page = ucfirst(strtolower($page));
	$class = "\\Page\\$page";

	if (!class_exists($class)) {
		\Base\Page::errorNotFound();
	}

	$inst = new $class();
	$inst->runWeb();
} catch (Exception $e) {
	\Base\Page::errorServerError();
}
