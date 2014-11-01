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

DROP TABLE IF EXISTS erb_page;
DROP TABLE IF EXISTS erb_author_prep;
DROP TABLE IF EXISTS erb_book;
DROP TABLE IF EXISTS erb_oairepo;

CREATE TABLE erb_oairepo (
	id SERIAL NOT NULL,
	url VARCHAR(256) NOT NULL,		-- OAI repository base URL
	name VARCHAR(256) NOT NULL,		-- Repository name for display
	metaformat VARCHAR(32) NOT NULL,	-- Metadata format to request
	lastcheck DATE NOT NULL,		-- Date from which to request more records
	PRIMARY KEY (id)
);

CREATE INDEX ON erb_oairepo (name);

CREATE TABLE erb_book (
	id SERIAL NOT NULL,
	title VARCHAR(1024) NOT NULL,
	kramerius_id VARCHAR(128) NOT NULL,
	web VARCHAR(256) NOT NULL,
	lang VARCHAR(32) NOT NULL,
	srcrepo INT NOT NULL,
	PRIMARY KEY (id),
	FOREIGN KEY (srcrepo) REFERENCES erb_oairepo (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE INDEX ON erb_book (title);
CREATE INDEX ON erb_book (kramerius_id);

CREATE TABLE erb_author_prep (
	id SERIAL NOT NULL,
	book INT NOT NULL,
	name VARCHAR(256) NOT NULL,
	PRIMARY KEY (id),
	FOREIGN KEY (book) REFERENCES erb_book (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE INDEX ON erb_author_prep (name);

CREATE TABLE erb_page (
	id SERIAL NOT NULL,
	book INT NOT NULL,
	page INT NOT NULL,
	content TEXT NOT NULL,
	image BYTEA NOT NULL,
	PRIMARY KEY (id),
	UNIQUE (book, page),
	FOREIGN KEY (book) REFERENCES erb_book (id) ON DELETE CASCADE ON UPDATE CASCADE
);
