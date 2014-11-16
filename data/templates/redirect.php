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
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>Erben</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="refresh" content="1; url=<?php echo $self->url; ?>" />
<script type="text/javascript">
	window.location.href = "<?php echo addslashes($self->_url); ?>"
</script>
</head>
<body>
<p>You should be redirected in a moment. Otherwise follow <a href="<?php echo $self->url; ?>">this link</a>.</p>
</body>
</html>
