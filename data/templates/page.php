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

$origclass = trim("pagerevision $self->origlink_class");
$origlink = sprintf($linkfmt, $self->origurl, tr('Original'));
$booklink = sprintf($linkfmt, $self->booklink, $self->title);
$tr_title = sprintf(tr('%s, page %s'), $booklink, $self->label);
$tr_imgalt = htmlspecialchars(tr('Scanned page image'));
$tr_noimg = tr('Image not available');
$tr_pagenum = tr('Page %s (%d/%d)');
$navfmt = "$firstlink $prevlink $tr_pagenum $nextlink $lastlink";

$pagecounter = p('<div class="pager">'.trim($navfmt).'</div>', $self->label, $self->pagenum, $self->pagecount);
$content = nl2br(trim($self->content));
?>
<h1><?php echo $tr_title; ?></h1>

<?php echo $pagecounter; ?>

<div class="pagerevs">
<?php
echo <<<SNIPPET
<div class="$origclass">
$origlink
</div>
$self->_revisions
SNIPPET;
?>
</div>

<table class="pageview"><tr>
<td>
<?php
if ($self->showcontent) {
	echo <<<SNIPPET
  <div class="pagetext">$content</div>
SNIPPET;
}

if (!empty($self->showform)) {
	$tr_typesetting = tr('This page contains complex typesetting');
	$tr_splitpara = tr('Last paragraph continues on next page');
	$tr_extrapage = tr('This entire page is not part of the main text (e.g. full-page illustration, colophon, table of contents etc.)');
	$tr_savepage = tr('Save');
	$tr_preview = tr('Preview');
	$tr_cancel = tr('Cancel edit');
	$val_typesetting = $self->form_typesetting ? 'checked="checked"' : '';
	$val_splitpara = $self->form_splitpara ? 'checked="checked"' : '';
	$val_extrapage = $self->form_extrapage ? 'checked="checked"' : '';

	$errsnip = empty($self->form_error) ? '' : "<div class=\"error\">$self->form_error</div>";

	echo <<<SNIPPET
<div class="editform">
  <form action="$self->form_action" method="post">
    $errsnip
    <textarea rows="30" cols="80" name="content">$self->form_content</textarea>
    <div class="metainfo">
      <div><input type="checkbox" name="typesetting" $val_typesetting> $tr_typesetting</div>
      <div><input type="checkbox" name="splitpara" $val_splitpara> $tr_splitpara</div>
      <div><input type="checkbox" name="extrapage" $val_extrapage> $tr_extrapage</div>
    </div>
    <div class="buttons">
      <input type="submit" name="savepage" value="$tr_savepage">
      <input type="submit" name="preview" value="$tr_preview">
      <input type="submit" name="cancel" value="$tr_cancel">
    </div>
  </form>
</div>
SNIPPET;
} else {
	$tr_editpage = tr('Edit');
	echo <<<SNIPPET
<div class="actionbuttons">
  <form action="$self->form_action" method="get">
    <input type="hidden" name="action" value="edit">
    <input type="submit" value="$tr_editpage">
  </form>
</div>
SNIPPET;
}
?>
</td>
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
