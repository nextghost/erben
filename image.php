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

if (empty($_GET['id'])) {
	header('HTTP/1.0 404 Not Found');
	die();
}

define('APP_BASEDIR', dirname(__FILE__));
require 'lib/init.php';

try {
	$db = new \Common\Database();
	$params = array('id' => $_GET['id']);
	$stmt = $db->query('SELECT image FROM erb_page WHERE id = :id', $params);
	$stmt->bindColumn(1, $data, PDO::PARAM_LOB);

	if (!$stmt->fetch()) {
		header('HTTP/1.0 404 Not Found');
		die();
	}
} catch (Exception $e) {
	header('HTTP/1.0 500 Internal Server Error');
	die();
}

header('Content-Type: image/png');
fpassthru($data);
