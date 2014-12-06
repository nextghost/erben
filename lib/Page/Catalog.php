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

class Catalog extends \Base\Page {
	const PAGESIZE = 100;

	protected static function pagecount() {
		$man = new \Common\BookManager();
		$count = $man->bookcount();
		$ret = intval(($count + self::PAGESIZE - 1) / self::PAGESIZE);
		return $ret > 1 ? $ret : 1;
	}

	public static function url($page = null) {
		$url = new \Common\NiceUrl();
		$url->setChunk(0, 'catalog');

		if (!empty($page)) {
			$url->setChunk(1, $page);
		}

		return $url->getUrl();
	}

	public function runWeb() {
		$path = self::pageUrl();
		$page = $path->getIdInt(1);
		$pagecount = self::pagecount();

		# Page out of range. Do *NOT* generate permanent redirect (301)
		# because it would break paging in some browsers after importing
		# a new batch of books.
		if (!empty($page) && $page > $pagecount) {
			self::redirect(self::url($pagecount), 302);
		}

		# Page in range, drop any description text after page number
		self::checkCanonicalUrl(self::url($page));
		$page = empty($page) ? 0 : $page - 1;

		$tpl = new \Web\Template('catalog.php');
		$man = new \Common\BookManager();
		$tpl->list = $man->booklist($page * self::PAGESIZE, self::PAGESIZE);

		if ($pagecount > 1) {
			$tpl->pagenum = $page + 1;
			$tpl->pagecount = $pagecount;
			$tpl->firsturl = $page > 0 ? self::url(1) : null;
			$tpl->prevurl = $page > 0 ? self::url($page) : null;
			$tpl->nexturl = $page < $pagecount - 1 ? self::url($page + 2) : null;
			$tpl->lasturl = $page < $pagecount - 1 ? self::url($pagecount) : null;
		} else {
			$tpl->pagenum = null;
			$tpl->pagecount = null;
			$tpl->firsturl = null;
			$tpl->prevurl = null;
			$tpl->nexturl = null;
			$tpl->lasturl = null;
		}

		$this->sendHtml($tpl, 'Book Catalog');
	}
}
