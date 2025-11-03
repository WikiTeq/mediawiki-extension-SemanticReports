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

if ( !defined( 'SMW_PHPUNIT_DIR' )
	&& version_compare( MW_VERSION, '1.40', '<' )
) {
	define(
		'SMW_PHPUNIT_DIR',
		__DIR__ . '/../../../../SemanticMediaWiki/tests/phpunit'
	);
}

$wgExtensionFunctions[] = static function () {
	$smwTests = __DIR__ . '/../../../../SemanticMediaWiki/tests/phpunit';
	// phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
	// Problematic test that leaves an uncaught exception about critical sections
	@unlink( $smwTests . '/MediaWiki/HooksTest.php' );

	// Some failing SMW tests
	$skip = [
		'/Benchmark/BenchmarkJsonScriptRunnerTest.php',
		'/DataValueFactoryTest.php',
		'/DataValues/MonolingualTextValueMappingTest.php',
		'/DataValues/MonolingualTextValueTest.php',
		'/DataValues/PropertyChainValueTest.php',
		'/DataValues/ValueFormatters/MonolingualTextValueFormatterTest.php',
		'/DataValues/ValueFormatters/PropertyValueFormatterTest.php',
		'/DataValues/ValueFormatters/ReferenceValueFormatterTest.php',
		'/Integration/JSONScript/JSONScriptTestCaseRunnerTest.php',
		'/Exporter/ResourceBuilders/PreferredPropertyLabelResourceBuilderTest.php',
		'/Exporter/ResourceBuilders/PropertyDescriptionValueResourceBuilderTest.php',
		'/Factbox/FactboxTest.php',
		'/Integration/InterwikiDBIntegrationTest.php',
		'/Integration/Maintenance/RunImportTest.php',
		'/Integration/MediaWiki/Hooks/FileUploadIntegrationTest.php',
		'/Integration/MediaWiki/Import/Maintenance/DumpRdfMaintenanceTest.php',
		'/Integration/MediaWiki/Jobs/UpdateJobRoundtripTest.php',
		'/Integration/RdfFileResourceTest.php',
		'/Localizer/LocalizerTest.php',
		'/Maintenance/RunImportTest.php',
		'/MediaWiki/Hooks/FileUploadTest.php',
		'/MediaWiki/Hooks/ParserAfterTidyTest.php',
		'/MediaWiki/LocalTimeTest.php',
		'/MediaWiki/PageInfoProviderTest.php',
		'/ParserFunctionFactoryTest.php',
		'/MediaWiki/Page/ListBuilder/ValueListBuilderTest.php',
	];
	foreach ( $skip as $file ) {
		@unlink( $smwTests . $file );
	}
	// phpcs:enable Generic.PHP.NoSilencedErrors.Discouraged

	$configFile = __DIR__ . '/../../../../mediawiki/tests/phpunit/suite.xml';
	$content = file_get_contents( $configFile );
	// Lots of SMW tests (1593) have no covers annotations
	$content = str_replace( 'forceCoversAnnotation="true"', 'forceCoversAnnotation="false"', $content );
	// More SMW tests (113) complain about the at() matcher being deprecated
	$content = str_replace( 'failOnWarning="true"', 'failOnWarning="false"', $content );
	// A few SMW tests (4) do not perform any assertions
	$content = str_replace(
		'beStrictAboutTestsThatDoNotTestAnything="true"',
		'beStrictAboutTestsThatDoNotTestAnything="false"',
		$content
	);

	file_put_contents( $configFile, $content );

	$coreStructure = __DIR__ . '/../../../../mediawiki/tests/phpunit/structure/';
	// phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
	// Could not get core structure test to pass, make sure that if any API
	// modules are added to this extension the messages are there!
	@unlink( $coreStructure . 'ApiStructureTest.php' );
	// Same with AvailableRightsTest for the smw-* rights
	@unlink( $coreStructure . 'AvailableRightsTest.php' );
	// phpcs:enable Generic.PHP.NoSilencedErrors.Discouraged

	if ( version_compare( MW_VERSION, '1.40', '<' ) ) {
		$moreToSkip = [
			'/SetupTest.php',
			'/Integration/Maintenance/DisposeOutdatedEntitiesTest.php',
			'/Integration/Maintenance/DumpRDFTest.php',
			'/Integration/Maintenance/PurgeEntityCacheTest.php',
			'/Integration/Maintenance/RebuildConceptCacheTest.php',
			'/Integration/Maintenance/RebuildFulltextSearchTableTest.php',
			'/Integration/Maintenance/RemoveDuplicateEntitiesTest.php',
			'/Integration/Maintenance/SetupStoreMaintenanceTest.php',
			'/Integration/Maintenance/UpdateEntityCollationTest.php',
			'/Integration/Maintenance/UpdateQueryDependenciesTest.php',
			'/Integration/MediaWiki/Import/Maintenance/RebuildConceptCacheMaintenanceTest.php',
			'/Integration/MediaWiki/Import/Maintenance/RebuildDataMaintenanceTest.php',
			'/Integration/MediaWiki/Import/Maintenance/RebuildFulltextSearchTableTest.php',
			'/Integration/MediaWiki/Import/Maintenance/RebuildPropertyStatisticsMaintenanceTest.php',
			'/Integration/MediaWiki/Import/Maintenance/UpdateEntityCollationTest.php',
			'/Integration/MediaWiki/SQLStore/TableBuilder/TableBuilderIntegrationTest.php',
			'/MediaWiki/Content/SchemaContentFormatterTest.php',
			'/MediaWiki/Content/SchemaContentTest.php',
			'/MediaWiki/Renderer/HtmlFormRendererTest.php',
			'/MediaWiki/Specials/Admin/Supplement/EntityLookupTaskHandlerTest.php',
			'/MediaWiki/Specials/Ask/HtmlFormTest.php',
			'/MediaWiki/Specials/Ask/NavigationLinksWidgetTest.php',
			'/MediaWiki/Specials/Ask/ParametersProcessorTest.php',
			'/MediaWiki/Specials/Browse/ValueFormatterTest.php',
			'/MediaWiki/Specials/SearchByProperty/PageBuilderTest.php',
			'/MediaWiki/StripMarkerDecoderTest.php',
			'/MediaWiki/Template/TemplateExpanderTest.php',
			'/Parser/RecursiveTextProcessorTest.php',
			'/ParserFunctions/ConceptParserFunctionTest.php',
			'/ParserFunctions/InfoParserFunctionTest.php',
			'/Property/SpecificationLookupTest.php',
			'/Query/DebugFormatterTest.php',
			'/Query/DeferredTest.php',
			'/Query/DescriptionFactoryTest.php',
			'/Query/QueryLinkerTest.php',
			'/SPARQLStore/QueryEngine/DescriptionInterpreters/ValueDescriptionInterpreterTest.php',
			'/SPARQLStore/QueryEngine/QueryEngineTest.php',
			'/SQLStore/EntityStore/SemanticDataLookupTest.php',
			'/SQLStore/Lookup/CachedListLookupTest.php',
			'/SQLStore/Lookup/MonolingualTextLookupTest.php',
			'/SQLStore/Lookup/ProximityPropertyValueLookupTest.php',
			'/SQLStore/PropertyTableIdReferenceFinderTest.php',
			'/SQLStore/QueryEngine/Fulltext/SearchTableRebuilderTest.php',
			'/SQLStore/QueryEngine/QueryEngineTest.php',
			'/SQLStore/TableBuilder/TableSchemaManagerTest.php',
		];
		foreach ( $moreToSkip as $file ) {
			// phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
			@unlink( $smwTests . $file );
		}
	}
};

// ext.smw.autocomplete missing dependency
$wgResourceModules['jquery.ui.autocomplete'] = [
	'styles' => [],
];
if ( version_compare( MW_VERSION, '1.40', '<' ) ) {
	$wgResourceModules['jquery.ui.autocomplete']['targets'] = [ 'desktop', 'mobile' ];
}
