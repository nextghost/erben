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
?>
<h1><a href="<?php echo $self->booklink; ?>"><?php echo $self->title; ?></a>, page <?php echo $self->page; ?></h1>

<table class="pageview"><tr>
<td><?php echo nl2br($self->content); ?></td>
<td class="center">
<?php
if ($self->has_image) {
	echo <<<SNIPPET
<a href="$self->imagelink"><img class="bookpage" src="$self->imagelink" alt="Scanned page image"/></a>
SNIPPET;
} else {
	echo "Image not available";
}
?>
</td>
</tr></table>
