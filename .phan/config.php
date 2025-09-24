<?php

// use phan config shipped with mediawiki core
$cfg = require __DIR__ . '/../vendor/mediawiki/mediawiki-phan-config/src/config.php';

// Add the dependencies
$cfg['directory_list'] = array_merge(
	$cfg['directory_list'],
	[
		'../../extensions/SemanticMediaWiki',
	]
);

$cfg['exclude_analysis_directory_list'] = array_merge(
	$cfg['exclude_analysis_directory_list'],
	[
		'../../extensions/SemanticMediaWiki',
	]
);

return $cfg;
