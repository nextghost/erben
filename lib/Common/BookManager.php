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

class BookManager extends \Base\DataManager {
	public function repoInfo($id) {
		$params = array('id' => $id);
		$stmt = $this->db->query('SELECT * FROM erb_oairepo WHERE id = :id', $params);
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);

		if (!$row) {
			throw new NotFoundException("BookManager: Repository $id does not exist");
		}

		return $row;
	}

	public function bookInfo($id) {
		$params = array('id' => $id);
		$stmt = $this->db->query('SELECT * FROM erb_book WHERE id = :id', $params);
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);

		if (!$row) {
			throw new NotFoundException("BookManager: Book $id does not exist");
		}

		return $row;
	}

	public function booklist($offset, $limit) {
		$params = array('offset' => $offset, 'limit' => $limit);
		$stmt = $this->db->query('SELECT * FROM erb_book ORDER BY title ASC, id ASC LIMIT :limit OFFSET :offset', $params);
		$ret = array();

		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$ret[] = new \Data\BookInfo($row);
		}

		return $ret;
#		return $stmt->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function bookcount() {
		$stmt = $this->db->query('SELECT count(*) as cnt FROM erb_book');
		$ret = $stmt->fetch(\PDO::FETCH_ASSOC);
		return $ret['cnt'];
	}

	/**
	 * Returns PNG page image data as PDO::PARAM_LOB file handle.
	 * Returns NULL if the page doesn't exist or has no image data.
	 */
	public function pageImage($pageid) {
		$params = array('id' => $pageid);
		$stmt = $this->db->query('SELECT image FROM erb_page WHERE id = :id', $params);
		$stmt->bindColumn(1, $data, \PDO::PARAM_LOB);

		if (!$stmt->fetch()) {
			return null;
		}

		return $data;
	}
}
