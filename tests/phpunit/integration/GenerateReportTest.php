<?php

namespace MediaWiki\Extension\SemanticReports\Tests\Integration;

use MediaWiki\Extension\SemanticReports\Maintenance\GenerateReport;
use MediaWiki\Tests\Maintenance\MaintenanceBaseTestCase;
use RuntimeException;

/**
 * @covers \MediaWiki\Extension\SemanticReports\Maintenance\GenerateReport
 * @group Database
 */
class GenerateReportTest extends MaintenanceBaseTestCase {

	protected function getMaintenanceClass() {
		return GenerateReport::class;
	}

	/** @dataProvider provideInvalidArgs */
	public function testInvalidArgs( array $args, string $expected ) {
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( "FATAL ERROR: $expected (exit code = 1)" );
		$this->maintenance->loadWithArgv( $args );
		$this->maintenance->execute();
	}

	public static function provideInvalidArgs() {
		yield 'Invalid output format' => [
			[ '--query', 'ignored', '--format', 'not-csv' ],
			'Only CSV output is supported',
		];
		yield 'Invalid query - braces' => [
			[ '--query', 'foo{bar', '--format', 'csv' ],
			'Query cannot contain curly braces, i.e. use "[[Category:Test]] [[Property:Test]]", ' .
				'not "{{#ask: [[Category:Test]] ... }}"',
		];
		yield 'Invalid query - pipe argument' => [
			[ '--query', 'foo|bar=baz', '--format', 'csv' ],
			'Query cannot contain pipe arguments aside from print requests, i.e. "|?Prop" is allowed,' .
				' but "|format=test" is not',
		];
	}

}
