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

/**
 * Helper class for building and parsing nice URLs
 */
class NiceUrl {
	private static $base = null;
	private $str = null;
	private $full = array();
	private $ids = array();
	const TITLE_MAXLEN = 32;

	public function __construct($strURL = null) {
		self::genbase();

		if (!is_null($strURL)) {
			$this->parse($strURL);
		}
	}

	private static function genbase() {
		if (!is_null(self::$base)) {
			return;
		}

		$base = dirname($_SERVER['PHP_SELF']);

		if ($base == '.' || $base == '\\') {
			$base = '/';
		} else if (!empty($base) && substr($base, -1) != '/') {
			$base .= '/';
		}

		self::$base = $base;
	}

	public function parse($strURL) {
		$prefix = substr($strURL, 0, strlen(self::$base) - 1) . '/';

		if ($prefix != self::$base) {
			throw new \Exception('NiceUrl::parse(): Cannot parse URL that doesn\'t belong to Erben');
		}

		if (self::$base == '/' && substr($strURL, 0, 1) != '/') {
			$suburl = $strUrl;
		} else {
			$suburl = substr($strURL, strlen(self::$base));
		}

		$path = preg_split('#/#', $suburl);
		$ids = array();

		foreach ($path as $key => $val) {
			$pos = strpos($val, '-');

			if ($pos !== false) {
				$val = substr($val, 0, $pos);
			}

			$ids[$key] = $val;
		}

		$this->str = $strURL;
		$this->full = $path;
		$this->ids = $ids;
	}

	public function getChunk($pos) {
		return isset($this->full[$pos]) ? $this->full[$pos] : null;
	}

	public function getIdStr($pos) {
		return isset($this->ids[$pos]) ? $this->ids[$pos] : null;
	}

	public function getIdInt($pos) {
		if (!isset($this->ids[$pos]) || $this->ids[$pos] == '') {
			return null;
		}

		$str = $this->ids[$pos];

		if (strspn($str, '0123456789') == strlen($str)) {
			return intval($str);
		}

		return null;
	}

	public function getUrl() {
		if (is_null($this->str)) {
			$this->str = self::$base . implode('/', $this->full);
		}

		return $this->str;
	}

	public function __toString() {
		return $this->getUrl();
	}

	public function chunkCount() {
		return count($this->full);
	}

	public function resize($newsize) {
		$size = $this->chunkCount();

		if ($size == $newsize) {
			return;
		}

		$this->str = null;

		while ($size > $newsize) {
			$size--;
			unset($this->full[$size]);
			unset($this->ids[$size]);
		}

		for ($i = $size; $i < $newsize; $i++) {
			$this->full[$i] = '';
			$this->ids[$i] = '';
		}
	}

	public function setChunk($pos, $id, $title = '') {
		if ($pos > $this->chunkCount()) {
			$this->resize($pos);
		}

		if (strpos($id, '/') !== false) {
			throw new \Exception('NiceUrl::setChunk(): ID must not contain any slashes.');
		}

		$chunk = $id;

		# Normalize title for use in URL
		if (!empty($title)) {
			$title = iconv('UTF-8', 'ASCII//TRANSLIT', $title);
			$title = strtolower($title);
			$title = preg_replace('/[^a-z0-9]+/', '-', $title);

			if (substr($title, 0, 1) == '-') {
				$title = substr($title, 1);
			}

			if (substr($title, -1) == '-') {
				$title = substr($title, 0, strlen($title) - 1);
			}

			$maxlen = self::TITLE_MAXLEN;

			if (strlen($title) > $maxlen) {
				$idx = strrpos($title, '-', $maxlen + 1 - strlen($title));
				$idx = $idx < ($maxlen / 2) ? $maxlen : $idx;
				$title = substr($title, 0, $idx);
			}

			if (!empty($title)) {
				$chunk .= '-' . $title;
			}
		}

		$this->str = null;
		$this->full[$pos] = $chunk;
		$this->ids[$pos] = $id;
	}
}
