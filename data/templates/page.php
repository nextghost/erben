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

$firstlink = p('<a href="%s">&laquo; First</a>', $self->firsturl);
$prevlink = p('<a href="%s">&lt; Previous</a>', $self->prevurl);
$nextlink = p('<a href="%s">Next &gt;</a>', $self->nexturl);
$lastlink = p('<a href="%s">Last &raquo;</a>', $self->lasturl);

$navfmt = <<<SNIPPET
$firstlink $prevlink Page %d (%d/%d) $nextlink $lastlink
SNIPPET;

$pagecounter = p('<div class="pager">'.trim($navfmt).'</div>', $self->page, $self->pagenum, $self->pagecount);
?>
<h1><a href="<?php echo $self->booklink; ?>"><?php echo $self->title; ?></a>, page <?php echo $self->page; ?></h1>

<?php echo $pagecounter; ?>

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

<?php echo $pagecounter; ?>
