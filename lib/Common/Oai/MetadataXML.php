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
namespace Common\Oai;

abstract class MetadataXML {
	protected $props = array();

	# Process metadata contents and fill properties
	protected abstract function load(\DOMDocument $doc, \DOMElement $context);

	public function __isset($name) {
		return isset($this->props['name']);
	}

	public function __get($name) {
		if (array_key_exists($name, $this->props)) {
			return $this->props[$name];
		}

		throw new \Exception("MetadataXML: This metadata object has no property called \"$name\"");
	}

	public function __construct(\DOMElement $root, $validate = true) {
		$doc = $root->ownerDocument;

		if (empty($doc)) {
			throw new \Exception('MetadataXML: Root node is not associated with any document');
		}

/* Kramerius generates broken METS documents, do not validate...
		if ($validate) {
			$this->validate($doc);
		}
*/

		$this->load($doc, $root);
	}

	protected function validate(\DOMDocument $doc) {
		if (!@$doc->schemaValidate(APP_BASEDIR . '/xsd/all.xsd')) {
			throw new \Exception('MetadataXML: Document validation failed');
		}
	}

	protected function singleNode(\DOMXPath $xpath, \DOMElement $context, $query) {
		$list = $xpath->query($query, $context);

		if ($list->length != 1) {
			throw new \Exception("MetadataXML: Query \"$query\" did not return exactly one node");
		}

		return $list->item(0);
	}

	protected function singleVal(\DOMXPath $xpath, \DOMElement $context, $query) {
		return $this->singleNode($xpath, $context, $query)->nodeValue;
	}
}
