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
namespace Web;

class HtmlMarkup extends \Base\MarkupParser {
	protected $elemtpl = array();

	public static function sanitize($input) {
		return preg_replace_callback('/\\[|\\]/', function($match) { return $match[0] == '[' ? '[(]' : '[)]'; }, $input);
	}

	public function __construct() {
		$this->elemtpl = array(
			'(' => '[',
			')' => ']',
			'b' => "<strong>%s</strong>",
			'i' => "<em>%s</em>",
			'*' => "<li>%s</li>\n",
			'list' => "<ul>%s</ul>",
			'extra' => "<div class=\"extra\">%s</div>\n\n",
			'section' => "<h2>%s</h2>\n\n",
			'chapter' => "<h1>%s</h1>\n\n",
		);

		$elements = array(
			'' => array(
				'children' => array('chapter', 'section', 'extra', 'list', '(', ')', 'b', 'i'),
			),
			'chapter' => array(
				'container' => self::CONTAINER_TOP,
				'pair' => true,
				'children' => array('(', ')', 'b', 'i'),
			),
			'section' => array(
				'container' => self::CONTAINER_TOP,
				'pair' => true,
				'children' => array('(', ')', 'b', 'i'),
			),
			'extra' => array(
				'container' => self::CONTAINER_TOP,
				'pair' => true,
				'children' => array('(', ')', 'b', 'i'),
			),
			'list' => array(
				'container' => self::CONTAINER_ANY,
				'pair' => true,
				'children' => array('*'),
				'no_cdata' => true
			),
			'*' => array(
				'container' => self::CONTAINER_ANY,
				'pair' => true,
				'children' => array('(', ')', 'b', 'i', 'list'),
			),
			'b' => array(
				'container' => self::CONTAINER_PARA,
				'pair' => true,
				'children' => array('(', ')', 'i'),
			),
			'i' => array(
				'container' => self::CONTAINER_PARA,
				'pair' => true,
				'children' => array('(', ')', 'b'),
			),
			'(' => array(
				'container' => self::CONTAINER_PARA,
				'pair' => false,
			),
			')' => array(
				'container' => self::CONTAINER_PARA,
				'pair' => false,
			),
		);

		parent::__construct($elements);
	}

	protected function processElement($name, $content) {
		if (!empty($this->elemtpl[$name])) {
			$this->curcontent .= sprintf($this->elemtpl[$name], $content);
		} else {
			throw new \Common\ParseErrorException(sprintf(tr("Invalid element [%s]"), $name));
		}
	}

	protected function processParagraphStart() {
		$this->curcontent .= '<p>';
	}

	protected function processParagraphEnd() {
		$this->curcontent .= "</p>\n\n";
	}

	protected function processCdata($input) {
		$this->beginParagraph();
		$this->curcontent .= htmlspecialchars($input);
	}
}
