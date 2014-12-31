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

class PageJobGen extends \Worker\Task {
	private $pages = array();

	public function run($input) {
		if (empty($input['book'])) {
			throw new \Exception('PageJobGen: Book ID not set');
		}

		$pagetypes = array('FrontCover' => 0, 'FrontEndSheet' => 0,
			'TitlePage' => 1, 'Blank' => 0, 'NormalPage' => 1,
			'BackEndSheet' => 0, 'BackCover' => 0
		);

		$bookman = new \Common\BookManager();
		$binfo = $bookman->bookInfo($input['book']);
		$rinfo = $bookman->repoInfo($binfo->srcrepo);
		$repo = new \Common\Oai\Repository($rinfo->url);
		$mets = $repo->getRecord($binfo->kramerius_id, $rinfo->metaformat);

		foreach ($mets->pages as $page) {
			$type = $page['type'];

			if (!isset($pagetypes[$type])) {
				$this->log[] = "PageJobGen: Warning: Page $id has unknown type \"$type\"";
			} else if (!$pagetypes[$type]) {
				continue;
			}

			if (empty($page['image'])) {
				throw new \Exception("PageJobGen: Page {$page['id']} has no image URL");
			}

			$tmp = array('book' => $input['book'],
				'order' => $page['order'],
				'text' => $page['text'],
				'image' => $page['image']);
			$this->pages[] = $tmp;
		}
	}

	public function saveResult(\Common\Database $db) {
		$jobman = new \Common\JobManager($db);

		foreach ($this->pages as $page) {
			$jobman->createImportPage($page['book'], $page['order'],
				$page['image'], $page['text']);
		}
	}
}
