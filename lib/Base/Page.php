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
namespace Base;

abstract class Page {
	private static $urldata = null;
	private $navigation = null;

	abstract public function runWeb();

	# FIXME: generate nice error pages using templates
	public static function errorBadRequest() {
		header('HTTP/1.0 400 Bad Request');
		die('400 Bad Request');
	}

	public static function errorForbidden() {
		header('HTTP/1.0 403 Forbidden');
		die('403 Forbidden');
	}

	public static function errorNotFound() {
		header('HTTP/1.0 404 Not Found');
		die('404 Not Found');
	}

	public static function errorServerError() {
		header('HTTP/1.0 500 Internal Server Error');
		die('500 Internal Server Error');
	}

	public static function pageUrl() {
		if (is_null(self::$urldata)) {
			if (!isset($_SERVER['REDIRECT_URL'])) {
				throw new \Exception('Erben was not installed correctly. Please contact server administrator.');
			}

			self::$urldata = new \Common\NiceUrl($_SERVER['REDIRECT_URL']);
		}

		# Prevent changes to the internal object
		return clone self::$urldata;
	}

	protected static function redirect($url, $statuscode = 303) {
		$scheme = empty($_SERVER['REQUEST_SCHEME']) ? 'http' : $_SERVER['REQUEST_SCHEME'];
		$host = empty($_SERVER['HTTP_HOST']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST'];
		$port = $_SERVER['SERVER_PORT'];
		$url = "$scheme://$host:$port$url";
		header('Location: ' . $url, true, $statuscode);
		$tpl = new \Web\Template('redirect.php');
		$tpl->url = $url;
		$tpl->_url = $url;
		die($tpl->render());
	}

	protected static function checkCanonicalUrl($canonUrl) {
		$url = self::pageUrl()->getUrl();

		if ($url != $canonUrl) {
			self::redirect($canonUrl, 301);
		}
	}

	protected function navigation() {
		if (is_null($this->navigation)) {
			# Prevent exception loop
			$this->navigation = '';
			$nav = new \Web\Template('navigation.php');
			$nav->url_index = \Page\Index::url();
			$nav->url_catalog = \Page\Catalog::url();
			$this->navigation = $nav;
		}

		return $this->navigation;
	}

	protected function sendHtml(\Web\Template $content, $title = '') {
		$layout = new \Web\Template('layout.php');
		$layout->title = $title;
		$layout->navigation = $this->navigation();
		$layout->content = $content;
		echo $layout->render();
	}
}
