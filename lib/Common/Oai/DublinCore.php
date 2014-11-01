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

class DublinCore extends MetadataXML {
	protected function singleVal(\DOMXPath $xpath, \DOMElement $context, $query) {
		$list = $xpath->query($query, $context);

		if ($list->length != 1) {
			throw new \Exception("DublinCore: Query \"$query\" did not return exactly one value");
		}

		return $list->item(0)->nodeValue;
	}

	protected function load(\DOMDocument $doc, \DOMElement $root) {
		$xpath = new \DOMXPath($doc);
		$oaiNS = 'http://www.openarchives.org/OAI/2.0/oai_dc/';
		$xpath->registerNamespace('oai_dc', $oaiNS);
		$xpath->registerNamespace('dc', 'http://purl.org/dc/elements/1.1/');

		if ($root->namespaceURI != $oaiNS || $root->localName != 'dc') {
			throw new \Exception('DublinCore: Root node is not a valid Dublin Core document root');
		}

		$this->props['title'] = $this->singleVal($xpath, $root, 'dc:title');
		$this->props['language'] = $this->singleVal($xpath, $root, 'dc:language');
		$this->props['url'] = $this->singleVal($xpath, $root, 'dc:identifier');

		$authornodes = $xpath->query('dc:creator', $root);
		$authors = array();

		foreach ($authornodes as $node) {
			$authors[] = $node->nodeValue;
		}

		$this->props['authors'] = $authors;
	}

}
