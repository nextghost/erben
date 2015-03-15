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

class MarcXml extends MetadataXML {
	protected function load(\DOMDocument $doc, \DOMElement $root) {
		$xpath = new \DOMXPath($doc);
		$marcNS = 'http://www.loc.gov/MARC21/slim';
		$xpath->registerNamespace('m', $marcNS);

		if ($root->namespaceURI != $marcNS || $root->localName != 'collection') {
			throw new \Exception('MarcXml: Root node is not a valid MARCXML document root');
		}

		$recnode = $this->singleNode($xpath, $root, 'm:record');
		$title = $this->singleVal($xpath, $recnode, "m:datafield[@tag = '245']/m:subfield[@code = 'a']");
		$partnodes = $xpath->query("m:datafield[@tag = '245']/m:subfield[@code = 'n']", $recnode);
		$bookname = $title;
		$parts = '';

		foreach ($partnodes as $node) {
			$parts .= (empty($parts) ? '' : ', ') . $node->nodeValue;
		}

		if (!empty($parts)) {
			$bookname .= " ($parts)";
		}

		$this->props['title'] = $bookname;

		$authornodes = $xpath->query("m:datafield[@tag = '100' and m:subfield[@code = 'e'] = 'Author']/m:subfield[@code = 'a']", $recnode);
		$authors = array();

		foreach ($authornodes as $node) {
			$authors[] = $node->nodeValue;
		}

		$this->props['authors'] = $authors;
	}

}
