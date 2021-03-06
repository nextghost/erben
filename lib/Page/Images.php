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

class Images extends \Base\Page {
	public static function imageUrl($id) {
		$url = new \Common\NiceUrl();
		$url->setChunk(0, 'images');
		$url->setChunk(1, $id);
		return $url->getUrl();
	}

	public function runWeb() {
		$path = self::pageUrl();
		$page = $path->getIdInt(1);
		self::checkCanonicalUrl(self::imageUrl($page));

		if (is_null($page)) {
			self::errorNotFound();
		}

		$bm = new \Common\BookManager();
		$fp = $bm->pageImage($page);

		if (is_null($fp)) {
			self::errorNotFound();
		}

		header('Content-Type: image/png');
		fpassthru($fp);
	}
}
