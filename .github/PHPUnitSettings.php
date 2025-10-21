<?php

// Extra settings loaded for PHPUnit tests - the
// `wikiteq/mediawiki-phpunit-action` enables the current extension
// automatically but we also need to load SemanticMediaWiki and set it up

// Unfortunately SemanticMediaWiki cannot be installed directly in the normal
// "extensions" directory since it gets downloaded *before* the rest of
// MediaWiki. This file is at
// /home/runner/work/mediawiki-extension-SemanticReports/mediawiki-extension-
//    SemanticReports/mediawiki/extensions/SemanticReports/.github/PHPUnitSettings.php
// and we install SemanticMediaWiki into the same directory as MediaWiki itself:
// So, go up 4 levels and then into the extension
wfLoadExtension(
	'SemanticMediaWiki',
	__DIR__ . '/../../../../SemanticMediaWiki/extension.json'
);
enableSemantics( 'example.org' );

$wgExtensionFunctions[] = static function () {
	$smwTests = __DIR__ . '/../../../../SemanticMediaWiki/tests/phpunit';
	// Problematic test that leaves an uncaught exception about critical sections
	// phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
	@unlink( $smwTests . '/MediaWiki/HooksTest.php' );

	// Lots of SMW tests (1593) have no covers annotations
	$configFile = __DIR__ . '/../../../../mediawiki/tests/phpunit/suite.xml';
	$content = file_get_contents( $configFile );
	$content = str_replace( 'forceCoversAnnotation="true"', 'forceCoversAnnotation="false"', $content );
	file_put_contents( $configFile, $content );
};
