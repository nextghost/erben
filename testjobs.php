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
	die("Run this script from command line.\n");
}

define('APP_BASEDIR', dirname(__FILE__));
require 'worker/init.php';
require 'lib/init.php';

try {
	$db = new \Common\Database();
	$db->beginTransaction();
	$stmt = $db->query('SELECT COUNT(*) AS cnt FROM erb_oairepo');
	$ret = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($ret['cnt'] != 0) {
		throw new Exception('Database already contains data, aborting.');
	}

	$params = array('url' => 'http://kramerius.nkp.cz/kramerius/oai',
		'name' => 'NKP', 'metaformat' => 'mets',
		'lastcheck' => '2005-01-01');
	$db->query('INSERT INTO erb_oairepo (url, name, metaformat, lastcheck) VALUES (:url, :name, :metaformat, :lastcheck)', $params);
	$repo = $db->lastInsertId('erb_oairepo_id_seq');

	$jm = new \Common\JobManager($db);
	$jm->createImportBookList($repo, '2010-04-22', '2010-04-22');
	$db->commit();
	echo "Test jobs have been created, now run worker.php from command line.\n";
} catch (Exception $e) {
	if (isset($db)) {
		$db->rollback();
	}

	fprintf(STDERR, "%s\n", $e->getMessage());
}
