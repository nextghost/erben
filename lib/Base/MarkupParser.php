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

abstract class MarkupParser {
	const CONTAINER_PARA = 1;
	const CONTAINER_TOP = -1;
	const CONTAINER_ANY = 0;

	const TOK_START = 1;
	const TOK_ELEMENT = 2;
	const TOK_CDATA = 3;

	protected $elements = array();
	private $stack = array();
	private $curelem = '';
	protected $curcontent = '';
	protected $inpara = 0;
	protected $lasttok = 0;

	public function __construct(array $elements) {
		if (empty($elements['']['children'])) {
			$tmp = $elements;
			unset($tmp['']);
			$elements['']['children'] = array_keys($tmp);
		}

		$this->elements = $elements;
	}

	public function parse($input) {
		$regexp = '\\[/?[^/\\[\\]]+\\]|[^\\[\\]\\n]*[^\\[\\]]';
		$input = trim($input);

		if (!preg_match("#^(?:$regexp)*$#u", $input)) {
			throw new \Common\ParseErrorException(tr('Incomplete tag in text'));
		}

		preg_match_all("#\\G$regexp#u", $input, $matches, PREG_SET_ORDER);

		$this->stack = array();
		$this->curelem = '';
		$this->curcontent = '';
		$this->inpara = 0;
		$this->lasttok = self::TOK_START;

		foreach ($matches as $token) {
			if ($token[0][0] == '[') {
				if ($token[0][1] != '/') {
					$this->beginElement(substr($token[0], 1, -1));
				} else {
					$this->endElement(substr($token[0], 2, -1));
				}

				$this->lasttok = self::TOK_ELEMENT;
			} else {
				$tmp = trim($token[0]);

				if (empty($this->curelem) && $tmp === '') {
					if ($this->lasttok == self::TOK_CDATA) {
						$this->endParagraph();
					}
				} else if (empty($this->curelem) || empty($this->elements[$this->curelem]['no_cdata'])) {
					$this->processCdata($token[0]);
				} else if ($tmp !== '') {
					throw new \Common\ParseErrorException(sprintf(tr("Element [%s] cannot contain text data."), $this->curelem));
				}

				$this->lasttok = self::TOK_CDATA;
			}
		}

		if (!empty($this->curelem)) {
			throw new \Common\ParseErrorException(sprintf(tr("Missing closing tag for element [%s]"), $this->curelem));
		}

		$this->endParagraph();
		return $this->curcontent;
	}

	protected function beginElement($name) {
		if (is_null($name) || !isset($this->elements[$name])) {
			throw new \Common\ParseErrorException(sprintf(tr("Invalid element [%s]"), $name));
		}

		$pardef = $this->elements[$this->curelem];
		$def = $this->elements[$name];

		if (!in_array($name, $pardef['children'])) {
			throw new \Common\ParseErrorException(sprintf(tr("Element [%s] used in wrong place"), $name));
		}

		if (!empty($def['container'])) {
			if ($def['container'] == self::CONTAINER_TOP) {
				$this->endParagraph();

				if ($this->inpara) {
					throw new \Common\ParseErrorException(sprintf(tr("Element [%s] used in wrong place"), $name));
				}
			} else if ($def['container'] == self::CONTAINER_PARA) {
				$this->beginParagraph();
			}
		}

		if (!$def['pair']) {
			$this->processElement($name, '');
			return;
		}

		$this->stack[] = array(
			'curelem' => $this->curelem,
			'curcontent' => $this->curcontent,
			'inpara' => $this->inpara
		);

		$this->curelem = $name;
		$this->curcontent = '';

		if (empty($def['container']) || $def['container'] == self::CONTAINER_TOP) {
			$this->inpara = 1;
		}
	}

	protected function endElement($name) {
		if (empty($this->curelem)) {
			throw new \Common\ParseErrorException(sprintf(tr("Unmatched closing tag [/%s]"), $name));
		} else if ($name !== $this->curelem) {
			throw new \Common\ParseErrorException(sprintf(tr("Closing tag mismatch: [%s]...[/%s]"), $this->curelem, $name));
		}

		$content = $this->curcontent;
		$state = array_pop($this->stack);
		$this->curelem = $state['curelem'];
		$this->curcontent = $state['curcontent'];
		$this->inpara = $state['inpara'];
		$this->processElement($name, $content);
	}

	protected function beginParagraph() {
		if (!$this->inpara) {
			$this->processParagraphStart();
			$this->inpara = 1;
		}
	}

	protected function endParagraph() {
		if ($this->inpara && empty($this->curelem)) {
			$this->processParagraphEnd();
			$this->inpara = 0;
		}
	}

	protected abstract function processElement($name, $content);
	protected abstract function processParagraphStart();
	protected abstract function processParagraphEnd();
	protected abstract function processCdata($input);
}
