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

define('S_IFMT', 0xf000);
define('S_IFDIR', 0x4000);
define('S_IFREG', 0x8000);

/**
 * Create escape sequences for double-quoted PHP strings
 */
function qqescape($str) {
	return addcslashes($str, "\0..\x1f\\\$\"");
}

function slurp($file, array &$ret) {
	$data = file_get_contents($file);

	if (empty($data)) {
		return;
	}

	preg_match_all('/\\btr\\b\\s*\\(\\s*("(?:\\\\.|[^\\\\"]*)*"|\'(?:\\\\.|[^\'\\\\]*)*\')\\s*(.)/mu', $data, $matches, PREG_SET_ORDER);

	foreach ($matches as $call) {
		$msg = $call[1];

		# Message is not complete, skip
		if ($call[2] != ')' && $call[2] != ',') {
			continue;
		}

		if ($msg[0] == '"') {
			# Message contains variable expansion, skip
			if (!preg_match('/^(?:\\\\.|[^\\\\$]*)*$/', $msg, $m2)) {
				continue;
			}

			$msg = preg_replace('/((?:[^\\\\]|\\G)(?:\\\\\\\\)*)(\\\\[^\\\\"nrtvef$x0-7])/', '$1\\$2', $msg);
		} else {
			$msg = preg_replace('/((?:[^\\\\]|\\G)(?:\\\\\\\\)*)(\\\\[^\\\\\'])/', '$1\\$2', $msg);
		}

		$msg = stripcslashes(substr($msg, 1, strlen($msg) - 2));

		if ($call[2] == ')') {
			$ret[$msg] = null;
		} else {
			$ret[$msg] = array('0' => null);
		}
	}
}

function dirwalk($path, &$ret) {
	$stat = stat($path);

	if (empty($stat)) {
		return;
	}

	$type = $stat['mode'] & S_IFMT;

	if ($type == S_IFREG && preg_match('/\.php$/iu', $path)) {
		slurp($path, $ret);
	} else if ($type == S_IFDIR) {
		$ents = scandir($path);

		if (empty($ents)) {
			return;
		}

		foreach ($ents as $item) {
			if ($item[0] != '.') {
				dirwalk($path . '/' . $item, $ret);
			}
		}
	}
}

function dump_msgfile($file, array $msglist) {
	$messages = array();
	$prologue = '';
	$ns = 'FIXME';

	if (!empty($file)) {
		$prologue = @file_get_contents($file);

		# Fill $messages with existing translations
		if (!empty($prologue)) {
			include $file;
		}

		if (preg_match('#([^\\\\/]*)\.php#iu', $file, $matches)) {
			$ns = $matches[1];
		}
	}

	if (empty($prologue)) {
		$prologue = <<<EOF
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
namespace Lang\\$ns;

function plural(\$num) {
	return '0';
}

# Any changes after the first appearance of "messages" variable other than
# translations will be lost during file update.

EOF;
	} else {
		$prologue = preg_replace('/\\$messages\\b.*/su', '', $prologue);
	}

	foreach ($messages as $key => $trans) {
		if (array_key_exists($key, $msglist)) {
			$msglist[$key] = $trans;
		}
	}

	$msgdef = '';

	foreach ($msglist as $key => $value) {
		$msgdef .= '$messages["' . qqescape($key) . '"]';

		if (is_null($value)) {
			$msgdef .= " = null";
		} else if (is_array($value)) {
			foreach ($value as $key => $val) {
				$msgdef .= '["' . qqescape($key) . '"] = ';

				if (is_null($val)) {
					$msgdef .= "null";
				} else {
					$msgdef .= '"' . qqescape($val) . '"';
				}
			}
		} else {
			$msgdef .= ' = "' . qqescape($value) . '"';
		}

		$msgdef .= ";\n";
	}

	if (!empty($file)) {
		file_put_contents($file, "$prologue$msgdef");
	} else {
		echo "$prologue$msgdef";
	}
}

$cmdopts = array_slice($_SERVER['argv'], 1);
$pathlist = array();
$outfiles = array();
$msglist = array();
reset($cmdopts);


for ($val = current($cmdopts); !is_null(key($cmdopts)); $val = next($cmdopts)) {
	if (empty($val)) {
		continue;
	} else if ($val[0] != '-') {
		$pathlist[] = $val;
	} else if ($val == '-o') {
		$arg = next($cmdopts);

		if (is_null($arg) || strlen($arg) == 0) {
			die("Invalid or missing argument for option -o\n");
		}

		$outfiles[] = $arg;
	} else {
		die("Invalid option $val\n");
	}
}

foreach ($pathlist as $file) {
	dirwalk($file, $msglist);
}

ksort($msglist);

if (empty($outfiles)) {
	dump_msgfile(null, $msglist);
}

foreach ($outfiles as $file) {
	dump_msgfile($file, $msglist);
}

/*
$msgdef = '';

foreach ($msglist as $key => $value) {
	$msgdef .= '$messages["' . qqescape($key) . '"] = ';
	$msgdef .= (is_null($value) ? "null" : '"' . qqescape($value) . '"') . ";\n";
}

echo $msgdef;
*/
