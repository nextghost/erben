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

$linkfmt = '<a href="%s">%s</a>';
$firstlink = p($linkfmt, $self->firsturl, tr('&laquo; First'));
$prevlink = p($linkfmt, $self->prevurl, tr('&lt; Previous'));
$nextlink = p($linkfmt, $self->nexturl, tr('Next &gt;'));
$lastlink = p($linkfmt, $self->lasturl, tr('Last &raquo;'));

$booklink = sprintf($linkfmt, $self->booklink, $self->title);
$tr_title = sprintf(tr('%s, page %s'), $booklink, $self->label);
$tr_imgalt = htmlspecialchars(tr('Scanned page image'));
$tr_noimg = tr('Image not available');
$tr_pagenum = tr('Page %s (%d/%d)');
$navfmt = "$firstlink $prevlink $tr_pagenum $nextlink $lastlink";

$pagecounter = p('<div class="pager">'.trim($navfmt).'</div>', $self->label, $self->pagenum, $self->pagecount);
?>
<h1><?php echo $tr_title; ?></h1>

<?php echo $pagecounter; ?>

<table class="pageview"><tr>
<td><?php echo nl2br($self->content); ?></td>
<td class="center">
<?php
if ($self->has_image) {
	echo <<<SNIPPET
<a href="$self->imagelink"><img class="bookpage" src="$self->imagelink" alt="$tr_imgalt"/></a>
SNIPPET;
} else {
	echo $tr_noimg;
}
?>
</td>
</tr></table>

<?php echo $pagecounter; ?>
