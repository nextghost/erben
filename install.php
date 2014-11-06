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

if (isset($_SERVER['argv'])) {
	die("Running this installer from command line is not allowed.\n");
}

define('APP_BASEDIR', dirname(__FILE__));
require 'lib/init.php';

function runDbScript(\Common\Database $db, $filename) {
	echo "<p>Running SQL script " . htmlspecialchars($filename) . "</p>\n";
	$data = file_get_contents($filename);
	$db->exec($data);
}

$baseurl = dirname($_SERVER['PHP_SELF']);

if (empty($baseurl) || $baseurl == '.') {
	$baseurl = '/';
} else if (substr($baseurl, -1) != '/') {
	$baseurl .= '/';
}

$htaccess = <<<EOF
DirectorySlash Off
Options -MultiViews
RewriteEngine On
RewriteOptions AllowNoSlash
RewriteBase $baseurl
RewriteRule ^(.*)$ index.php [QSA,END]
EOF;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>Erben installer</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<?php
try {
	$db = new \Common\Database();
	runDbScript($db, 'db/job.sql');
	runDbScript($db, 'db/pages.sql');
	$params = array('url' => 'http://kramerius.nkp.cz/kramerius/oai',
		'name' => 'NKP', 'metaformat' => 'mets',
		'lastcheck' => '2005-01-01');
	$db->query('INSERT INTO erb_oairepo (url, name, metaformat, lastcheck) VALUES (:url, :name, :metaformat, :lastcheck)', $params);

	if (@file_put_contents('.htaccess', $htaccess) === false) {
		echo "<p>Could not create .htaccess file in current directory. To complete the installation, you'll have to create the file manually with the following content:</p>\n<pre>" . htmlspecialchars($htaccess) . "</pre>\n<p>Then you can <a href=\"" . htmlspecialchars($baseurl) . "\">continue to Erben</a>.</p>\n";
	} else {
		echo "<p>Erben installation successful. You can now <a href=\"" . htmlspecialchars($baseurl) . "\">continue to Erben</a>.</p>\n";
	}

	echo "<p><b>Note:</b> If the above link gives you <i>Internal Server Error</i>, check Apache error log. Apache system configuration files may prevent use of some directives in .htaccess files. Disabling mod_dir and mod_negotiation for this directory is necessary to make Erben work. If they cannot be disabled in .htaccess file, you will have to edit the system configuration files.</p>";
} catch (Exception $e) {
	echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>
</body>
</html>
