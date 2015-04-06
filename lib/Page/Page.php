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
	private $error = '';

	public static function url($pageid, $revid = null) {
		$man = new \Common\BookManager();
		$pinfo = $man->pageInfo($pageid);
		$binfo = $man->bookInfo($pinfo->book);
		$url = new \Common\NiceUrl();
		$url->setChunk(0, 'page');
		$url->setChunk(1, $pageid, $binfo->title);

		if (!is_null($revid)) {
			$rinfo = $man->revisionInfo($revid);

			if ($rinfo->page != $pageid) {
				throw new \Common\NotFoundException("Revision $revid belongs to another page");
			}

			$url->setChunk(2, $rinfo->id);
		}

		return $url->getUrl();
	}

	public function runWeb() {
		$path = self::pageUrl();
		$pageid = $path->getIdInt(1);
		$revid = $path->getIdInt(2);

		if (is_null($pageid)) {
			self::errorNotFound();
		}

		try {
			$canonurl = self::url($pageid, $revid);
			$man = new \Common\BookManager();
			$pinfo = $man->pageInfo($pageid);
			$binfo = $man->bookInfo($pinfo->book);
		} catch (\Common\NotFoundException $e) {
			self::errorNotFound();
		}

		$post_content = empty($_POST['content']) ? '' : $_POST['content'];
		$post_typesetting = !empty($_POST['typesetting']);
		$post_splitpara = !empty($_POST['splitpara']);
		$post_extrapage = !empty($_POST['extrapage']);

		if (!empty($_POST['cancel'])) {
			self::redirect($canonurl, 303);
		} else if (!empty($_POST['savepage'])) {
			try {
				$newrev = $man->savePageRevision($pageid, $revid, $post_content, $post_typesetting, $post_splitpara, $post_extrapage);
				self::redirect(self::url($pageid, $newrev), 303);
			} catch (\Exception $e) {
				$this->error = tr('Could not save changes. Please try again later.');
			}
		}

		try {
			self::checkCanonicalUrl($canonurl);

			if (is_null($revid)) {
				$content = $man->pageContent($pageid);
			} else {
				$rinfo = $man->revisionInfo($revid);
				$content = $rinfo->content;
			}

			$nav = $man->pagenav($pageid);
			$pcount = $man->pagecount($pinfo->book);
			$revisions = $man->revisionList($pageid);
		} catch (\Common\NotFoundException $e) {
			self::errorNotFound();
		}

		if (!empty($_POST['savepage']) || !empty($_POST['preview'])) {
			$content = empty($_POST['content']) ? '' : $_POST['content'];
		}

		$tpl = new \Web\Template('page.php');
		$tpl->merge($pinfo->htmldata());
		$tpl->title = $binfo->title;
		$tpl->content = $content;
		$tpl->showcontent = 1;
		$tpl->imagelink = $pinfo->has_image ? Images::imageUrl($pageid) : null;
		$tpl->origurl = self::url($pageid);
		$tpl->firsturl = is_null($nav['first']) ? null : self::url($nav['first']);
		$tpl->prevurl = is_null($nav['prev']) ? null : self::url($nav['prev']);
		$tpl->nexturl = is_null($nav['next']) ? null : self::url($nav['next']);
		$tpl->lasturl = is_null($nav['last']) ? null : self::url($nav['last']);
		$tpl->pagenum = $nav['pos'];
		$tpl->pagecount = $pcount;
		$tpl->revisions = $revisions;

		$tpl->form_action = $path->getUrl();
		$action = empty($_GET['action']) ? '' : $_GET['action'];

		if ($action == 'edit' || !empty($_POST)) {
			$tpl->form_error = $this->error;
			$tpl->form_content = empty($_POST) ? $content : $post_content;

			if (empty($_POST) && !empty($rinfo)) {
				$tpl->form_typesetting = $rinfo->typesetting;
				$tpl->form_splitpara = $rinfo->splitpara;
				$tpl->form_extrapage = $rinfo->extrapage;
			} else {
				$tpl->form_typesetting = $post_typesetting;
				$tpl->form_splitpara = $post_splitpara;
				$tpl->form_extrapage = $post_extrapage;
			}

			$tpl->showform = 1;

			if (empty($_POST['preview'])) {
				$tpl->showcontent = 0;
			}
		} else {
			$tpl->showform = 0;
		}

		$this->sendHtml($tpl, $binfo->title);
	}
}
