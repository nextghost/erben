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

class ImportPage extends \Worker\Task {
	private $rowdata = null;

	public function run($input) {
		if (empty($input['book']) || !isset($input['order']) || !isset($input['label']) || empty($input['image']) || empty($input['text'])) {
			throw new \Exception('ImportPage: Missing required parameters');
		}

		$http = new \Common\Downloader();
		$text = $http->get($input['text']);
		$djvu = $http->get($input['image']);

		$pdesc = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => STDERR);
		$pipes = array();
		$conv = proc_open('convert - png:-', $pdesc, $pipes);

		if (!is_resource($conv)) {
			throw new \Exception('ImportPage: Could not start image conversion subprocess');
		}

		if (fwrite($pipes[0], $djvu) != strlen($djvu)) {
			fclose($pipes[0]);
			fclose($pipes[1]);
			proc_terminate($conv);
			throw new \Exception('ImportPage: Error sending input to conversion subprocess');
		}

		fclose($pipes[0]);
		$png = stream_get_contents($pipes[1]);
		fclose($pipes[1]);
		$ret = proc_close($conv);

		if ($png === false) {
			throw new \Exception('ImportPage: Error reading output from conversion subprocess');
		}

		if ($ret) {
			throw new \Exception("ImportPage: Conversion subprocess returned error code $ret");
		}

		$this->rowdata = array('book' => $input['book'],
			'page' => $input['order'], 'label' => $input['label'],
			'content' => $text, 'image' => $png);
	}

	public function saveResult(\Common\Database $db) {
		$stmt = $db->prepare('INSERT INTO erb_page (book, page, label, content, image) VALUES (:book, :page, :label, :content, :image)');
		$stmt->bindParam(':book', $this->rowdata['book']);
		$stmt->bindParam(':page', $this->rowdata['page']);
		$stmt->bindParam(':label', $this->rowdata['label']);
		$stmt->bindParam(':content', $this->rowdata['content']);
		$stmt->bindParam(':image', $this->rowdata['image'], \PDO::PARAM_LOB);

		if (!$stmt->execute()) {
			throw new \Common\DbException($stmt->errorInfo());
		}
	}
}
