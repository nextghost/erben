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

class BookManager {
	public function repoInfo($id) {
		$db = new Database();
		$params = array('id' => $id);
		$stmt = $db->query('SELECT * FROM erb_oairepo WHERE id = :id', $params);
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);

		if (!$row) {
			throw new \Exception("BookManager: Repository $id does not exist");
		}

		return $row;
	}

	public function bookInfo($id) {
		$db = new Database();
		$params = array('id' => $id);
		$stmt = $db->query('SELECT * FROM erb_book WHERE id = :id', $params);
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);

		if (!$row) {
			throw new \Exception("BookManager: Book $id does not exist");
		}

		return $row;
	}
}
