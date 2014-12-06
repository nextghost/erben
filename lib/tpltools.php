<?php

function p($format) {
	$args = func_get_args();

	foreach ($args as $item) {
		if (is_null($item)) {
			return null;
		}
	}

	return vsprintf($format, array_slice($args, 1));
}
