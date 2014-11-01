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

class Downloader {
	private $curl;

	public function __construct() {
		$this->curl = curl_init();
		$this->setopt(CURLOPT_FAILONERROR, true);
		$this->setopt(CURLOPT_FOLLOWLOCATION, true);
		$this->setopt(CURLOPT_NOSIGNAL, true);
		$this->setopt(CURLOPT_RETURNTRANSFER, true);
		$this->setopt(CURLOPT_SAFE_UPLOAD, true);
		$this->setopt(CURLOPT_MAXREDIRS, 32);
	}

	public function __destruct() {
		curl_close($this->curl);
	}

	public function setopt($option, $value) {
		if (!curl_setopt($this->curl, $option, $value)) {
			throw new \Exception("curl_setopt($option) failed");
		}
	}

	private function exec($url) {
		$this->setopt(CURLOPT_URL, $url);
		$ret = curl_exec($this->curl);

		if (is_null($ret) || !$ret) {
			throw new \Exception(curl_error($this->curl), curl_errno($this->curl));
		}

		return $ret;
	}

	public function get($url) {
		$this->setopt(CURLOPT_HTTPGET, true);
		return $this->exec($url);
	}

	public function post($url, $data) {
		$this->setopt(CURLOPT_POST, true);
		$this->setopt(CURLOPT_POSTFIELDS, $data);
		return $this->exec($url);
	}

	public static function buildUrl($baseUrl, array $params) {
		$argstr = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
		return $baseUrl . (strpos($baseUrl, '?') === false ? '?' : '&') . $argstr;
	}
}
