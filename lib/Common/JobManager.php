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

class JobManager extends \Base\DataManager {
	protected function insertJob($jobtype, $input) {
		$params = array('jobtype' => $jobtype,
			'input' => serialize($input));
		$this->db->query('INSERT INTO erb_jobs (jobtype, input) VALUES (:jobtype, :input)', $params);
	}

	public function createHarvestJobGen($repo) {
		$this->insertJob('HarvestJobGen', array('repo' => $repo));
	}

	public function createImportBookList($repo, $from, $until) {
		$input = array('repo' => $repo, 'from' => $from,
			'until' => $until);
		$this->insertJob('ImportBookList', $input);
	}

	public function createPageJobGen($book) {
		$this->insertJob('PageJobGen', array('book' => $book));
	}

	public function createImportPage($book, $order, $label, $image, $text) {
		$input = array('book' => $book, 'order' => $order,
			'label' => $label, 'image' => $image, 'text' => $text);
		$this->insertJob('ImportPage', $input);
	}
}
