<?php

namespace MediaWiki\Extension\SemanticReports\Maintenance;

use MediaWiki\Extension\SemanticReports\SemanticReports;
use MediaWiki\MediaWikiServices;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}
require_once "$IP/maintenance/Maintenance.php";

// @codingStandardsIgnoreStart

/**
 * @method \MediaWiki\MediaWikiServices getServiceContainer() available in 1.40+
 */
class GenerateReport extends \Maintenance {
// @codingStandardsIgnoreEnd

	/**
	 * SemanticReportsReport constructor.
	 *
	 * @param null $args
	 */
	public function __construct( $args = null ) {
		parent::__construct();
		$this->addDescription( 'Generates a report based on the semantic query' );
		$this->addOption( 'query', 'Query to run: "{{#ask: ...}}"', true, true, 'q' );
		$this->addOption( 'format', 'Output format: csv', true, true, 'f' );
		$this->addOption( 'output', 'Output file', false, true, 'o' );
		$this->requireExtension( 'SemanticReports' );
		if ( $args ) {
			$this->loadWithArgv( $args );
		}
	}

	/**
	 * @return null
	 */
	public function execute() {
		// REL1_39 compat
		if ( !method_exists( $this, 'getServiceContainer' ) ) {
			/** @var SemanticReports $semanticReports */
			$semanticReports = MediaWikiServices::getInstance()->get( 'SemanticReports' );
		} else {
			/** @var SemanticReports $semanticReports */
			$semanticReports = $this->getServiceContainer()->get( 'SemanticReports' );
		}
		$query = $this->getOption( 'query' );
		$format = $this->getOption( 'format' );

		if ( $format !== 'csv' ) {
			$this->fatalError( 'Only CSV output is supported' );
		}

		// enforce that the query does not contain curly braces
		if ( str_contains( $query, '{' ) ) {
			$this->fatalError(
				'Query cannot contain curly braces, i.e. use "[[Category:Test]] [[Property:Test]]", ' .
				'not "{{#ask: [[Category:Test]] ... }}"'
			);
		}

		// enforce that the query does not contain pipe arguments aside from print requests
		if ( preg_match( '/\|[^?]+/', $query ) ) {
			$this->fatalError(
				'Query cannot contain pipe arguments aside from print requests, i.e. "|?Prop" is allowed,' .
				' but "|format=test" is not'
			);
		}

		$result = $semanticReports->getReportData( $query, $format );
		if ( $result === false ) {
			$this->fatalError( 'Error generating report' );
		}

		// phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.Found
		if ( $filename = $this->getOption( 'output' ) ) {
			// output to the file
			if ( !file_put_contents( $filename, $result ) ) {
				$this->fatalError( "Error saving report to $filename" );
			}
			$this->outputChanneled( "Report saved to $filename" );
		} else {
			// output to the stdout
			$this->output( $result );
		}
	}

}

$maintClass = GenerateReport::class;
require_once RUN_MAINTENANCE_IF_MAIN;
