#!/usr/bin/php
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

if (empty($_SERVER['argv'])) {
	die('Running worker process directly through web server is not allowed.');
}

# Bye apache, hello init!
if (pcntl_fork()) {
	return;
}

# Be nicer to other processes
@proc_nice(10);
define('APP_BASEDIR', dirname(__FILE__));
require 'worker/init.php';
require 'lib/init.php';

try {
	$disp = new \Worker\Dispatcher();
	$disp->run();
} catch (Exception $e) {
	fprintf(STDERR, "%s\n", $e->getMessage());
}
