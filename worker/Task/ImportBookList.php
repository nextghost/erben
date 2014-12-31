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
namespace Task;

class ImportBookList extends \Worker\Task {
	private $books = array();

	public function run($input) {
		if (empty($input['repo']) || empty($input['from']) || empty($input['until'])) {
			throw new \Exception('ImportBookList: Missing required parameters');
		}

		$bookman = new \Common\BookManager();
		$rinfo = $bookman->repoInfo($input['repo']);
		$repo = new \Common\Oai\Repository($rinfo->url);
		$booklist = $repo->listRecords('type:monograph', $rinfo->metaformat, $input['from'], $input['until']);

		foreach ($booklist as $id => $book) {
			$info = $book->info;
			$tmp = array('title' => $info->title, 'kramid' => $id,
				'web' => $info->url, 'lang' => $info->language,
				'srcrepo' => $input['repo'],
				'authors' => $info->authors);
			$this->books[] = $tmp;
		}

		# Let the remote server rest for a while
		sleep(10);
	}

	public function saveResult(\Common\Database $db) {
		foreach ($this->books as $item) {
			$params = array('kramid' => $item['kramid'], 'srcrepo' => $item['srcrepo']);
			$stmt = $db->query('SELECT id FROM erb_book WHERE kramerius_id = :kramid AND srcrepo = :srcrepo', $params);

			if ($stmt->fetch()) {
				continue;
			}

			$params = $item;
			unset($params['authors']);
			$db->query('INSERT INTO erb_book (title, kramerius_id, web, lang, srcrepo) VALUES (:title, :kramid, :web, :lang, :srcrepo)', $params);
			$id = $db->lastInsertId('erb_book_id_seq');

			foreach ($item['authors'] as $author) {
				$params = array('book' => $id, 'name' => $author);
				$db->query('INSERT INTO erb_author_prep (book, name) VALUES (:book, :name)', $params);
			}
		}
	}
}
