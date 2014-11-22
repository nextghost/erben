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
namespace Page;

class Book extends \Base\Page {
	public static function url($bookid) {
		$man = new \Common\BookManager();
		$info = $man->bookInfo($bookid);
		$url = new \Common\NiceUrl();
		$url->setChunk(0, 'book');
		$url->setChunk(1, $bookid, $info['title']);
		return $url->getUrl();
	}

	public function runWeb() {
		$path = self::pageUrl();
		$bookid = $path->getIdInt(1);

		if (is_null($bookid)) {
			self::errorNotFound();
		}

		try {
			$canonurl = self::url($bookid);
			self::checkCanonicalUrl($canonurl);
			$man = new \Common\BookManager();
			$info = $man->bookInfo($bookid);
		} catch (\Common\NotFoundException $e) {
			self::errorNotFound();
		}

		$tpl = new \Web\Template('book.php');
		$tpl->title = $info['title'];
		$tpl->srcurl = $info['web'];
		$this->sendHtml($tpl, $info['title']);
	}
}
