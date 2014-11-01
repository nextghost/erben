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

class Repository {
	private $url;
	private $http;

	protected static function makeXPath(\DOMDocument $doc) {
		$xpath = new \DOMXPath($doc);
		$xpath->registerNamespace('oai', 'http://www.openarchives.org/OAI/2.0/');
		$xpath->registerNamespace('m', 'http://www.loc.gov/METS/');
		return $xpath;
	}

	public function __construct($url) {
		$this->url = $url;
		$this->http = new \Common\Downloader();
	}

	protected function request($verb, array $params = array()) {
		$params['verb'] = $verb;
		$data = $this->http->get($this->http->buildUrl($this->url, $params));
		$xml = new \DOMDocument();
		$xml->loadXML($data);

/* Kramerius generates broken METS documents, do not validate...
		if (!@$xml->schemaValidate(APP_BASEDIR . '/xsd/all.xsd')) {
			throw new \Exception("Oai\\Repository: Response to $verb request is not a valid OAI-PMH 2.0 document");
		}
*/

		return $xml;
	}

	protected function checkErrors(\DOMXPath $xpath, $verb) {
		$errors = $xpath->query('/oai:OAI-PMH/oai:error/@code');

		if ($errors->length) {
			$err = $errors->item(0)->nodeValue;
			throw new \Exception("Oai\\Repository: Request \"$verb\" returned error \"$err\"");
		}
	}

	public function repoinfo() {
		$xml = $this->request('Identify');
		$xpath = self::makeXPath($xml);
		$this->checkErrors($xpath, 'Identify');
		$ret = array();
		$ret['repotime'] = $xpath->evaluate('string(/oai:OAI-PMH/oai:responseDate)');
		$ret['name'] = $xpath->evaluate('string(/oai:OAI-PMH/oai:Identify/oai:repositoryName)');
		$version = $xpath->evaluate('string(/oai:OAI-PMH/oai:Identify/oai:protocolVersion)');
		$ret['startdate'] = $xpath->evaluate('string(/oai:OAI-PMH/oai:Identify/oai:earliestDatestamp)');
		$ret['dateformat'] = $xpath->evaluate('string(/oai:OAI-PMH/oai:Identify/oai:granularity)');

		if ($version != '2.0') {
			throw new \Exception("Oai\\Repository: Repository uses unsupported protocol version: $version");
		}

		$xml = $this->request('ListMetadataFormats');
		$xpath = self::makeXPath($xml);
		$this->checkErrors($xpath, 'ListMetadataFormats');
		$metsNS = 'http://www.loc.gov/METS/';
		$ret['metaformat'] = $xpath->evaluate("string(/oai:OAI-PMH/oai:ListMetadataFormats/oai:metadataFormat[oai:metadataNamespace = '$metsNS']/oai:metadataPrefix)");

		if (empty($ret['metaformat'])) {
			throw new \Exception('Oai\\Repository: This repository does not provide metadata in METS format');
		}

		$xml = $this->request('ListSets');
		$xpath = self::makeXPath($xml);
		$this->checkErrors($xpath, 'ListSets');
		$test = $xpath->evaluate("boolean(/oai:OAI-PMH/oai:ListSets/oai:set[oai:setSpec = 'type:monograph'])");

		if (!$test) {
			throw new \Exception('Oai\\Repository: This repository does not contain monographs');
		}

		return $ret;
	}

	public function listRecords($set, $metaformat, $from, $until) {
		$params = array('set' => $set, 'metadataPrefix' => $metaformat,
			'from' => $from, 'until' => $until);
		$ret = array();
		$xml = $this->request('ListRecords', $params);
		$xpath = self::makeXPath($xml);

		if ($xpath->evaluate("boolean(/oai:OAI-PMH/oai:error[@code != 'noRecordsMatch'])")) {
			$this->checkErrors($xpath, 'ListRecords');
		}

		while(true) {
			$records = $xpath->query("/oai:OAI-PMH/oai:ListRecords/oai:record[not(oai:header/@status = 'deleted')]");

			foreach ($records as $rec) {
				$key = $xpath->evaluate('string(oai:header/oai:identifier)', $rec);

				if (empty($key)) {
					throw new \Exception('Oai\\Repository: Record with no ID');
				}

				$metadoc = $xpath->query('oai:metadata/m:mets', $rec);

				if ($metadoc->length != 1) {
					throw new \Exception("Oai\\Repository: Record $key has bad metadata structure");
				}

				$ret[$key] = new MetsDoc($metadoc->item(0), false);
			}

			$reselem = $xpath->query('/oai:OAI-PMH/oai:ListRecords/oai:resumptionToken');
			$restok = null;

			if ($reselem->length) {
				$restok = $reselem->item(0)->nodeValue;
			}

			if (empty($restok)) {
				break;
			}

			$params = array('resumptionToken' => $restok);
			$xml = $this->request('ListRecords', $params);
			$xpath = self::makeXPath($xml);
			$this->checkErrors($xpath, 'ListRecords');
		}

		return $ret;
	}

	public function getRecord($id, $metaformat) {
		$params = array('identifier' => $id,
			'metadataPrefix' => $metaformat);
		$xml = $this->request('GetRecord', $params);
		$xpath = self::makeXPath($xml);

		$this->checkErrors($xpath, 'GetRecord');
		$records = $xpath->query('/oai:OAI-PMH/oai:GetRecord/oai:record/oai:metadata/m:mets');
		return new MetsDoc($records->item(0), false);
	}
}
