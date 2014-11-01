# Erben

A web-based tool for creating full e-books from single pages digitized by Czech national Library.

## How it should work

First, this system will import the entire catalog of books from digitized archives of Czech National Library. The CzNL metadata archive is available at http://kramerius.nkp.cz/kramerius/oai (see http://www.openarchives.org/OAI/openarchivesprotocol.html for protocol specification).

Next, an admin will select a batch of books to work on, the system will download their contents and convert DjVu images of digitized pages to PNG for viewing through web browser without any additional plugins. Storing the contents of all books in PNG format would require nearly 20 TB of storage space so only a handful of books will be open for work at any time. When a book is finished, PNG images will be deleted to free space for other books but the full history of text data will be kept.

Users will then edit the OCR text wiki-style to clean any mistakes in it, add basic formatting markup and vote for pages that they believe are clean and finished.

As the last step, the cleaned text of all pages in each book will be combined together and exported for advanced formatting and conversion into an actual e-book format (PDF, epub etc.).

## System requirements

- Linux/BSD
- PostgreSQL database
- PHP 5.3 or newer with the following extensions:
  - curl
  - date
  - dom
  - libxml
  - pcntl (command line only, not listed as module in phpinfo() page)
  - pcre
  - PDO
  - pdo_pgsql
  - posix
  - xml

## Installation instructions

1. Write database connection details into config/config.php.example and rename it to config/config.php
2. Run install.php from command line

## TODO

- [x] Implement background task processor
  - [x] Harvest job generator
  - [x] Book harvester
  - [x] Page import job generator
  - [x] Single page importer
- [ ] Implement web interface
  - [ ] Account and session management
  - [ ] List of authors and books
  - [ ] Book detail page
  - [ ] Book page editor
  - [x] Book page image display
  - [ ] User wishlist and book popularity statistics
  - [ ] User contribution leaderboard
  - [ ] System administration
    - [ ] Job and worker process management
    - [ ] Repository and harvesting management
    - [ ] Book content import management
  - [ ] Duplicate author entry merging tool
