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

DROP TABLE IF EXISTS erb_joberror;
DROP TABLE IF EXISTS erb_jobrun;
DROP TABLE IF EXISTS erb_jobs;
DROP TABLE IF EXISTS erb_workers;

CREATE TABLE erb_workers (
	pid INT NOT NULL,
	heartbeat TIMESTAMP NOT NULL,
	PRIMARY KEY (pid)
);

CREATE INDEX ON erb_workers (heartbeat);

CREATE TABLE erb_jobs (
	id SERIAL NOT NULL,
	jobtype VARCHAR(32) NOT NULL,
	input TEXT NOT NULL,
	PRIMARY KEY (id)
);

CREATE INDEX ON erb_jobs (jobtype, id);

CREATE TABLE erb_jobrun (
	job INT NOT NULL,
	worker INT NOT NULL,
	started TIMESTAMP NOT NULL,
	PRIMARY KEY (job),
	FOREIGN KEY (job) REFERENCES erb_jobs (id) ON DELETE RESTRICT ON UPDATE RESTRICT,
	FOREIGN KEY (worker) REFERENCES erb_workers (pid) ON DELETE RESTRICT ON UPDATE RESTRICT
);

CREATE INDEX ON erb_jobrun (started);

-- No foreign keys to preserve old error messages
CREATE TABLE erb_joberror (
	id SERIAL NOT NULL,
	job INT NOT NULL,
	pid INT NOT NULL,
	errdate TIMESTAMP NOT NULL,
	message TEXT NOT NULL,
	PRIMARY KEY (id)
);

CREATE INDEX ON erb_joberror (job);
CREATE INDEX ON erb_joberror (errdate);
