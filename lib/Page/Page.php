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

class Page extends \Base\Page {
	public static function url($pageid) {
		$man = new \Common\BookManager();
		$pinfo = $man->pageInfo($pageid);
		$binfo = $man->bookInfo($pinfo->book);
		$url = new \Common\NiceUrl();
		$url->setChunk(0, 'page');
		$url->setChunk(1, $pageid, $binfo->title);
		return $url->getUrl();
	}

	public function runWeb() {
		$path = self::pageUrl();
		$pageid = $path->getIdInt(1);

		if (is_null($pageid)) {
			self::errorNotFound();
		}

		try {
			$canonurl = self::url($pageid);
			self::checkCanonicalUrl($canonurl);
			$man = new \Common\BookManager();
			$pinfo = $man->pageInfo($pageid);
			$binfo = $man->bookInfo($pinfo->book);
			$content = $man->pageContent($pageid);
			$nav = $man->pagenav($pageid);
			$pcount = $man->pagecount($pinfo->book);
		} catch (\Common\NotFoundException $e) {
			self::errorNotFound();
		}

		$tpl = new \Web\Template('page.php');
		$tpl->merge($pinfo->htmldata());
		$tpl->title = $binfo->title;
		$tpl->content = $content;
		$tpl->imagelink = $pinfo->has_image ? Images::imageUrl($pageid) : null;
		$tpl->firsturl = is_null($nav['first']) ? null : self::url($nav['first']);
		$tpl->prevurl = is_null($nav['prev']) ? null : self::url($nav['prev']);
		$tpl->nexturl = is_null($nav['next']) ? null : self::url($nav['next']);
		$tpl->lasturl = is_null($nav['last']) ? null : self::url($nav['last']);
		$tpl->pagenum = $nav['pos'];
		$tpl->pagecount = $pcount;
		$this->sendHtml($tpl, $binfo->title);
	}
}
