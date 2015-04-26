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
$messages["Cannot show content due to error in markup: %s"] = "Obsah nelze zobrazit kvůli chybě formátování: %s";
$messages["Changes could not be saved due to error in markup: %s"] = "Změny nelze uložit kvůli chybě formátování: %s";
$messages["Closing tag mismatch: [%s]...[/%s]"] = "Překřížený otevírací a uzavírací tag: [%s]...[/%s]";
$messages["Could not save changes. Please try again later."] = "Nepodařilo se uložit změny. Zkuste to prosím později.";
$messages["Each book is split into multiple separate pages. The initial text for each page is raw output from OCR software which needs to be corrected and formatted by hand. The purpose of Erben is to make it as easy as possible for you to help."] = "Každá kniha je rozdělena na jednotlivé stránky. Počáteční text u každé stránky je neupravený výstup z OCR programu, který je třeba ručně opravit a naformátovat. Cílem Erbenu je, abyste mohli pomoct co nejjednodušeji.";
$messages["Edit"] = "Upravit";
$messages["Element [%s] cannot contain text data."] = "Element [%s] nesmí obsahovat textová data";
$messages["Element [%s] used in wrong place"] = "Element [%s] je použit na nepovoleném místě";
$messages["Erben is named in honor of 19th century Czech writer Karel Jaromír Erben. His most famous works were collections of Czech folklore and poems with themes related to folklore."] = "Erben je pojmenován na počest českého spisovatele 19. století Karla Jaromíra Erbena. Mezi jeho nejslavnější díla patří sbírky českých lidových bájí a poezie na motivy lidové mytologie.";
$messages["Help us convert scanned paper books into full e-books. Books in our catalog have been only partially digitized, we need your help to fix any OCR conversion errors and add basic formatting to the text."] = "Pomozte nám vyrobit z naskenovaných papírových knih plnohodnotné e-booky. Knihy v našem seznamu jsou digitalizované jen částečně, pomozte nám opravit chyby OCR konverze a doplnit základní formátování.";
$messages["Image not available"] = "Obrázek není dostupný";
$messages["Incomplete tag in text"] = "V textu je neúplný tag";
$messages["Invalid element [%s]"] = "Neplatný element [%s]";
$messages["Last &raquo;"] = "Poslední &raquo;";
$messages["Last paragraph continues on next page"] = "Poslední odstavec pokračuje na další straně";
$messages["Main Page"] = "Úvodní stránka";
$messages["Missing closing tag for element [%s]"] = "Chybí uzavírací tag pro element [%s]";
$messages["Next &gt;"] = "Další &gt;";
$messages["Original"] = "Původní text";
$messages["Page %d/%d"] = "Strana %d/%d";
$messages["Page %s (%d/%d)"] = "Strana %s (%d/%d)";
$messages["Pages"] = "Stránky";
$messages["Preview"] = "Náhled";
$messages["Save"] = "Uložit";
$messages["Scanned page image"] = "Naskenovaný obrázek stránky";
$messages["Some books in the catalog may not have any pages yet. Page images take a lot of space so we'll add those pages later when other books get finished. Books that are open for corrections and formatting are highlighted in the catalog."] = "Některé knihy v našem seznamu možná ještě nemají žádné stránky. Naskenované obrázky stránek zabírají hodně místa, takže chybějící stránky doplníme později, až se po dokončení jiných knih uvolní místo. Knihy, u kterých se můžete pustit do oprav a formátování, jsou v seznamu zvýrazněné.";
$messages["Source repository"] = "Zdrojová data";
$messages["This entire page is not part of the main text (e.g. full-page illustration, colophon, table of contents etc.)"] = "Celá tato stránka není součástí hlavního textu (např. celostránková ilustrace, tiráž, seznam kapitol atd.)";
$messages["This page contains complex typesetting"] = "Tato stránka obsahuje složité formátování";
$messages["Unmatched closing tag [/%s]"] = "Přebytečný uzavírací tag [/%s]";
$messages["Welcome to Erben"] = "Vítá Vás Erben";
$messages["Well, not yet. Erben is still under construction and we need developers and designers before we start digitizing our first books. This installation is only a sandbox where you can freely explore all implemented features and look for things you could improve. If you'd like to help with development of Erben, visit the <a href=\"https://github.com/nextghost/erben\">Erben repository on GitHub</a>. All edits you make here will be deleted during the next database schema update. They will not become part of any e-book."] = "Totiž, zatím ještě ne. Erben je stále ve vývoji a než začneme vážně digitalizovat první knihy, budeme potřebovat vývojáře a grafiky. Tato instalace je jen pokusné hřiště, kde si můžete volně zkoušet implementované funkce a hledat, co byste mohli vylepšit. Pokud se chcete zapojit do vývoje Erbenu, navštivte <a href=\"https://github.com/nextghost/erben\">repozitář Erbenu na GitHubu</a>. Všechny Vaše úpravy se během příští změny databázových struktur vymažou. Do e-booků se žádná z nich nedostane.";
