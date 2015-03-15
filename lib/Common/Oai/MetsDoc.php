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

class MetsDoc extends MetadataXML {
	protected function fileList(\DOMXPath $xpath, \DOMElement $grp) {
		$ret = array();

		# Ignore group structure
		$files = $xpath->query("descendant::m:file[@USE = 'Page']", $grp);

		foreach ($files as $node) {
			$entry = array();
			$id = $node->getAttribute('ID');

			if (empty($id)) {
				throw new \Exception('MetsDoc: File record with no ID');
			}

			$links = $xpath->query("m:FLocat[@LOCTYPE = 'URL']/@xl:href", $node);

			foreach ($links as $url) {
				$entry[] = $url->nodeValue;
			}

			if (empty($entry)) {
				throw new \Exception("MetsDoc: File \"$id\" has no URLs");
			}

			$ret[$id] = $entry;
		}

		return $ret;
	}

	protected function loadPages(\DOMXPath $xpath, \DOMElement $root) {
		$imgnodes = $xpath->query("m:fileSec/m:fileGrp[@USE = 'img']", $root);
		$txtnodes = $xpath->query("m:fileSec/m:fileGrp[@USE = 'txt']", $root);

		if ($imgnodes->length != 1 || $txtnodes->length != 1) {
			throw new \Exception('MetsDoc: Unexpected file structure');
		}

		$imglist = $this->fileList($xpath, $imgnodes->item(0));
		$txtlist = $this->fileList($xpath, $txtnodes->item(0));

		$pagenodes = $xpath->query("m:structMap[@TYPE = 'Pages']/m:div[@ID = 'SMP']/m:div", $root);
		$pagelist = array();

		foreach ($pagenodes as $page) {
			$tmp = array('text' => null, 'image' => null);
			$tmp['id'] = $id = $page->getAttribute('ID');
			$tmp['type'] = $page->getAttribute('TYPE');
			$tmp['order'] = $order = $page->getAttribute('ORDER');

			if (!preg_match('/^[0-9]+$/', $tmp['order'])) {
				throw new \Exception("MetsDoc: Page \"$id\" has invalid order \"$order\"");
			}

			$filenodes = $xpath->query('m:fptr/@FILEID', $page);

			foreach ($filenodes as $fptr) {
				$val = $fptr->nodeValue;

				if (isset($imglist[$val])) {
					if (isset($tmp['image'])) {
						throw new \Exception("Page $id has multiple image files");
					}

					$tmp['image'] = $imglist[$val][0];
				} else if (isset($txtlist[$val])) {
					if (isset($tmp['text'])) {
						throw new \Exception("Page $id has multiple text files");
					}

					$tmp['text'] = $txtlist[$val][0];
				}
			}

			$pagelist[] = $tmp;
		}

		$this->props['pages'] = $pagelist;
	}

	protected function loadInfo(\DOMXPath $xpath, \DOMElement $root) {
		$dcnode = $this->singleNode($xpath, $root, "m:dmdSec[@ID = 'DMD_DC']/m:mdWrap/m:xmlData/oai_dc:dc");
		$marcnode = $this->singleNode($xpath, $root, "m:dmdSec[@ID = 'DMD_MARC']/m:mdWrap/m:xmlData/marc:collection");
		$dcinfo = new DublinCore($dcnode, false);
		$marcinfo = new MarcXml($marcnode, false);
		$this->props['title'] = $marcinfo->title;
		$this->props['authors'] = $dcinfo->authors;
		$this->props['language'] = $dcinfo->language;
		$this->props['url'] = $dcinfo->url;
	}

	protected function load(\DOMDocument $doc, \DOMElement $root) {
		$xpath = new \DOMXPath($doc);
		$metsNS = 'http://www.loc.gov/METS/';
		$xpath->registerNamespace('m', $metsNS);
		$xpath->registerNamespace('xl', 'http://www.w3.org/1999/xlink');
		$xpath->registerNamespace('oai_dc', 'http://www.openarchives.org/OAI/2.0/oai_dc/');
		$xpath->registerNamespace('marc', 'http://www.loc.gov/MARC21/slim');

		if ($root->namespaceURI != $metsNS || $root->localName != 'mets') {
			throw new \Exception('MetsDoc: Root node is not a valid METS document root');
		}

		$this->loadInfo($xpath, $root);
		$this->loadPages($xpath, $root);
	}
}
