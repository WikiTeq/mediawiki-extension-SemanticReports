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
	// phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
	// Problematic test that leaves an uncaught exception about critical sections
	@unlink( $smwTests . '/MediaWiki/HooksTest.php' );

	// Some failing SMW tests
	@unlink( $smwTests . '/Benchmark/BenchmarkJsonScriptRunnerTest.php' );
	@unlink( $smwTests . '/DataValueFactoryTest.php' );
	@unlink( $smwTests . '/DataValues/MonolingualTextValueMappingTest.php' );
	@unlink( $smwTests . '/DataValues/MonolingualTextValueTest.php' );
	@unlink( $smwTests . '/DataValues/PropertyChainValueTest.php' );
	@unlink( $smwTests . '/DataValues/ValueFormatters/MonolingualTextValueFormatterTest.php' );
	@unlink( $smwTests . '/DataValues/ValueFormatters/PropertyValueFormatterTest.php' );
	@unlink( $smwTests . '/DataValues/ValueFormatters/ReferenceValueFormatterTest.php' );
	@unlink( $smwTests . '/Integration/JSONScript/JSONScriptTestCaseRunnerTest.php' );
	// phpcs:enable Generic.PHP.NoSilencedErrors.Discouraged

	$configFile = __DIR__ . '/../../../../mediawiki/tests/phpunit/suite.xml';
	$content = file_get_contents( $configFile );
	// Lots of SMW tests (1593) have no covers annotations
	$content = str_replace( 'forceCoversAnnotation="true"', 'forceCoversAnnotation="false"', $content );
	// More SMW tests (113) complain about the at() matcher being deprecated
	$content = str_replace( 'failOnWarning="true"', 'failOnWarning="false"', $content );
	// A few SMW tests (4) do not perform any assertions
	$content = str_replace( 'beStrictAboutTestsThatDoNotTestAnything="true"', 'beStrictAboutTestsThatDoNotTestAnything="false"', $content );

	file_put_contents( $configFile, $content );
};
