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

class HarvestJobGen extends \Worker\Task {
	private $repo = null;
	private $startdate = null;
	private $enddate = null;

	public function run($input) {
		if (empty($input['repo'])) {
			throw new \Exception('HarvestJobGen: Repository ID not set');
		}

		$this->repo = $input['repo'];
		$bookman = new \Common\BookManager();
		$reg = $bookman->repoInfo($input['repo']);
		$this->startdate = $reg->lastcheck;
		$repo = new \Common\Oai\Repository($reg->url);
		$info = $repo->repoinfo();
		$tz = new \DateTimeZone('UTC');
		$dt = new \DateTime($info['repotime'], $tz);
		$this->enddate = $dt->format('Y-m-d');
	}

	public function saveResult(\Common\Database $db) {
		$tz = new \DateTimeZone('UTC');
		$int1 = new \DateInterval('P1D');
		$int6 = new \DateInterval('P6D');
		$cur = new \DateTime($this->startdate, $tz);
		$jobman = new \Common\JobManager($db);

		do {
			$from = $cur->format('Y-m-d');
			$cur->add($int6);
			$until = $cur->format('Y-m-d');
			$cur->add($int1);
			$jobman->createImportBookList($this->repo, $from, $until);
		} while($until < $this->enddate);

		$params = array('id' => $this->repo, 'checked' => $this->enddate);
		$db->query('UPDATE erb_oairepo SET lastcheck = :checked WHERE id = :id', $params);
	}
}
