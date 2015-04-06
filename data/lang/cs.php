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
namespace Lang\cs;

function plural($num) {
	if ($num == 1) {
		return '0';
	} else if ($num > 1 && $num < 5) {
		return '1';
	} else {
		return '2';
	}
}

# Any changes after the first appearance of "messages" variable other than
# translations will be lost during file update.
$messages["%s, page %s"] = "%s, strana %s";
$messages["&laquo; First"] = "&laquo; První";
$messages["&lt; Previous"] = "&lt; Předchozí";
$messages["Book Catalog"] = "Seznam knih";
$messages["Cancel edit"] = "Zavřít bez uložení";
$messages["Could not save changes. Please try again later."] = 'Nepodařilo se uložit změny. Zkuste to prosím později.';
$messages["Edit"] = 'Upravit';
$messages["Hello world!"] = "Ahoj světe!";
$messages["Image not available"] = "Obrázek není dostupný";
$messages["Last &raquo;"] = "Poslední &raquo;";
$messages["Last paragraph continues on next page"] = 'Poslední odstavec pokračuje na další straně';
$messages["Main Page"] = "Úvodní stránka";
$messages["Next &gt;"] = "Další &gt;";
$messages["Original"] = 'Původní text';
$messages["Page %d/%d"] = "Strana %d/%d";
$messages["Page %s (%d/%d)"] = "Strana %s (%d/%d)";
$messages["Pages"] = "Stránky";
$messages["Preview"] = 'Náhled';
$messages["Save"] = 'Uložit';
$messages["Scanned page image"] = "Naskenovaný obrázek stránky";
$messages["Source repository"] = "Zdrojová data";
$messages["This entire page is not part of the main text (e.g. full-page illustration, colophon, table of contents etc.)"] = 'Celá tato stránka není součástí hlavního textu (např. celostránková ilustrace, tiráž, seznam kapitol atd.)';
$messages["This page contains complex typesetting"] = 'Tato stránka obsahuje složité formátování';
