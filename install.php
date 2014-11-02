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

define('APP_BASEDIR', dirname(__FILE__));
require 'lib/init.php';

function runDbScript(\Common\Database $db, $filename) {
	echo "Running SQL script $filename\n";
	$data = file_get_contents($filename);
	$db->exec($data);
}

try {
	$db = new \Common\Database();
	runDbScript($db, 'db/job.sql');
	runDbScript($db, 'db/pages.sql');
	$params = array('url' => 'http://kramerius.nkp.cz/kramerius/oai',
		'name' => 'NKP', 'metaformat' => 'mets',
		'lastcheck' => '2005-01-01');
	$db->query('INSERT INTO erb_oairepo (url, name, metaformat, lastcheck) VALUES (:url, :name, :metaformat, :lastcheck)', $params);
} catch (Exception $e) {
	die($e->getMessage());
}

echo "Erben installation successful.\n";
