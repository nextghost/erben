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

# PDO wrapper which turns any error into exception
class Database {
	private static $conn = null;

	public static function connection() {
		if (is_null(self::$conn)) {
			$cfg = new Config();
			$conf = $cfg->config();

			if (empty($conf['db']['dsn'])) {
				throw new Exception('Database connection not configured');
			}

			$dbconf = $conf['db'];
			$dsn = $dbconf['dsn'];
			$user = empty($dbconf['username']) ? '' : $dbconf['username'];
			$pass = empty($dbconf['password']) ? '' : $dbconf['password'];
			$opts = empty($dbconf['options']) ? array() : $dbconf['options'];
			self::$conn = new \PDO($dsn, $user, $pass, $opts);
		}

		return self::$conn;
	}

	public function exec($sql) {
		$db = self::connection();
		$ret = $db->exec($sql);

		if ($ret === false) {
			throw new DbException($db->errorInfo());
		}

		return $ret;
	}

	public function prepare($sql) {
		$db = self::connection();
		$stmt = $db->prepare($sql);

		if (!$stmt) {
			throw new DbException($db->errorInfo());

		}

		return $stmt;
	}

	public function query($sql, array $params = array()) {
		$stmt = $this->prepare($sql);

		if (!$stmt->execute($params)) {
			throw new DbException($stmt->errorInfo());
		}

		return $stmt;
	}

	public function beginTransaction() {
		$db = self::connection();

		if (!$db->beginTransaction()) {
			throw new DbException($db->errorInfo());
		}
	}

	public function commit() {
		$db = self::connection();

		if (!$db->commit()) {
			throw new DbException($db->errorInfo());
		}
	}

	public function rollback() {
		$db = self::connection();

		if (!$db->rollback()) {
			throw new DbException($db->errorInfo());
		}
	}

	public function lastInsertId($name = null) {
		$db = self::connection();

		return $db->lastInsertId($name);
	}
}
