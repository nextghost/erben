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
namespace Data;

class PageInfo extends \Base\StyledObject {
	public function __construct(array $data) {
		$keys = array('id', 'book', 'page', 'label', 'has_image');
		parent::__construct($data, $keys);
	}

	public function htmldata() {
		$ret = parent::htmldata();
		$ret->link = \Page\Page::url($this->data['id']);
		$ret->booklink = \Page\Book::url($this->data['book']);
		return $ret;
	}

	protected function style_default(\Web\HtmlData $self) {
		return <<<SNIPPET
<span class="pagelink">
<a href="$self->link">$self->label</a>
</span>
SNIPPET;
	}
}
