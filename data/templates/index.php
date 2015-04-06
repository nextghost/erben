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

if (empty($self) || !$self instanceOf \Web\Template) {
	throw new Exception('Templates must be called using \\Web\\Template class.');
}

$tr_title = tr('Welcome to Erben');

$tr_intro = tr('Help us convert scanned paper books into full e-books. Books in our catalog have been only partially digitized, we need your help to fix any OCR conversion errors and add basic formatting to the text.');

$tr_books = tr('Each book is split into multiple separate pages. The initial text for each page is raw output from OCR software which needs to be corrected and formatted by hand. The purpose of Erben is to make it as easy as possible for you to help.');

$tr_stages = tr('Some books in the catalog may not have any pages yet. Page images take a lot of space so we\'ll add those pages later when other books get finished. Books that are open for corrections and formatting are highlighted in the catalog.');

$tr_projectname = tr('Erben is named in honor of 19th century Czech writer Karel Jaromír Erben. His most famous works were collections of Czech folklore and poems with themes related to folklore.');
?>
<h1><?php echo $tr_title; ?></h1>

<p><?php echo $tr_intro; ?></p>
<p><?php echo $tr_books; ?></p>
<p><?php echo $tr_stages; ?></p>
<p><?php echo $tr_projectname; ?></p>
