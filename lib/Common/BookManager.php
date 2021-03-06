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
	protected static $repoCache = array();
	protected static $bookCache = array();
	protected static $pageCache = array();
	protected static $revisionCache = array();

	public function repoInfo($id) {
		if (self::$useCache && isset(self::$repoCache[$id])) {
			return self::$repoCache[$id];
		}

		$params = array('id' => $id);
		$stmt = $this->db->query('SELECT * FROM erb_oairepo WHERE id = :id', $params);
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);

		if (!$row) {
			throw new NotFoundException("BookManager: Repository $id does not exist");
		}

		$ret = new \Data\RepoInfo($row);

		if (self::$useCache) {
			self::$repoCache[$ret->id] = $ret;
		}

		return $ret;
	}

	public function bookInfo($id) {
		if (self::$useCache && isset(self::$bookCache[$id])) {
			return self::$bookCache[$id];
		}

		$params = array('id' => $id);
		$stmt = $this->db->query('SELECT * FROM erb_book WHERE id = :id', $params);
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);

		if (!$row) {
			throw new NotFoundException("BookManager: Book $id does not exist");
		}

		$ret = new \Data\BookInfo($row);

		if (self::$useCache) {
			self::$bookCache[$ret->id] = $ret;
		}

		return $ret;
	}

	public function booklist($offset, $limit) {
		$params = array('offset' => $offset, 'limit' => $limit);
		$stmt = $this->db->query('SELECT * FROM erb_book ORDER BY title ASC, id ASC LIMIT :limit OFFSET :offset', $params);
		$ret = array();

		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$tmp = new \Data\BookInfo($row);
			$ret[] = $tmp;

			if (self::$useCache) {
				self::$bookCache[$tmp->id] = $tmp;
			}
		}

		return $ret;
	}

	public function bookcount() {
		$stmt = $this->db->query('SELECT count(*) as cnt FROM erb_book');
		$ret = $stmt->fetch(\PDO::FETCH_ASSOC);
		return $ret['cnt'];
	}

	public function pagelist($book) {
		$params = array('book' => $book);
		$stmt = $this->db->query('SELECT id, book, page, label, image IS NOT NULL AS has_image FROM erb_page WHERE book = :book ORDER BY page ASC', $params);
		$ret = array();

		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$tmp = new \Data\PageInfo($row);
			$ret[] = $tmp;

			if (self::$useCache) {
				self::$pageCache[$tmp->id] = $tmp;
			}
		}

		return $ret;
	}

	public function pagecount($book) {
		$params = array('book' => $book);
		$stmt = $this->db->query('SELECT count(*) as cnt FROM erb_page WHERE book = :book', $params);
		$ret = $stmt->fetch(\PDO::FETCH_ASSOC);
		return $ret['cnt'];
	}

	public function pagenav($pageid) {
		$info = $this->pageInfo($pageid);
		$params = array('book' => $info->book, 'page' => $info->page);
		$subsql = 'SELECT MIN(page) as first, MAX(page) as last, COUNT(*) AS cnt FROM erb_page WHERE book = :book AND';
		$stmt = $this->db->query("SELECT MIN(a.id) AS first, MAX(b.id) AS prev, MIN(c.id) AS next, MAX(d.id) AS last, MIN(pred.cnt)+1 AS pos FROM (($subsql page < :page) AS pred CROSS JOIN ($subsql page > :page) AS succ) LEFT JOIN erb_page AS a ON a.book = :book AND pred.first = a.page LEFT JOIN erb_page AS b ON b.book = :book AND pred.last = b.page LEFT JOIN erb_page AS c ON c.book = :book AND succ.first = c.page LEFT JOIN erb_page AS d ON d.book = :book AND succ.last = d.page", $params);
		return $stmt->fetch(\PDO::FETCH_ASSOC);
	}

	public function pageInfo($id) {
		if (self::$useCache && isset(self::$pageCache[$id])) {
			return self::$pageCache[$id];
		}

		$params = array('id' => $id);
		$stmt = $this->db->query('SELECT id, book, page, label, image IS NOT NULL AS has_image FROM erb_page WHERE id = :id', $params);
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);

		if (!$row) {
			throw new NotFoundException("BookManager: Page $id does not exist");
		}

		$ret = new \Data\PageInfo($row);

		if (self::$useCache) {
			self::$pageCache[$ret->id] = $ret;
		}

		return $ret;
	}

	public function pageContent($pageid) {
		$params = array('id' => $pageid);
		$stmt = $this->db->query('SELECT content FROM erb_page WHERE id = :id', $params);
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);

		if (!$row) {
			throw new NotFoundException("BookManager: Page $id does not exist");
		}

		return $row['content'];
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

	public function savePageRevision($pageid, $parent, $content, $typesetting, $splitpara, $extrapage) {
		# FIXME: add user ID of current user
		$params = array('page_id' => $pageid,
			'parent' => $parent, 'content' => $content,
			'typesetting' => $typesetting ? 1 : 0,
			'splitpara' => $splitpara ? 1 : 0,
			'extrapage' => $extrapage ? 1 : 0);
		$this->db->query('INSERT INTO erb_pagerevision (page, parent, created, content, typesetting, splitpara, extrapage) VALUES (:page_id, :parent, NOW(), :content, :typesetting, :splitpara, :extrapage)', $params);
		return $this->db->lastInsertId('erb_pagerevision_id_seq');
	}

	public function revisionInfo($id) {
		if (self::$useCache && isset(self::$revisionCache[$id])) {
			return self::$revisionCache[$id];
		}

		$params = array('id' => $id);
		$stmt = $this->db->query('SELECT * FROM erb_pagerevision WHERE id = :id', $params);
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);

		if (!$row) {
			throw new NotFoundException("BookManager: Revision $id does not exist");
		}

		$ret = new \Data\RevisionInfo($row);

		if (self::$useCache) {
			self::$revisionCache[$ret->id] = $ret;
		}

		return $ret;
	}

	public function revisionList($pageid) {
		$params = array('page' => $pageid);
		$stmt = $this->db->query('SELECT * FROM erb_pagerevision WHERE page = :page ORDER BY created ASC, id ASC', $params);
		$ret = array();

		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$tmp = new \Data\RevisionInfo($row);
			$ret[$tmp->id] = $tmp;

			if (self::$useCache) {
				self::$revisionCache[$tmp->id] = $tmp;
			}
		}

		return $ret;
	}
}
