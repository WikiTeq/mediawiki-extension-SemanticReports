<?php

namespace MediaWiki\Extension\SemanticReports\Tests\Integration;

use MediaWiki\Extension\SemanticReports\Maintenance\GenerateReport;
use MediaWiki\Extension\SemanticReports\SemanticReports;
use MediaWiki\MediaWikiServices;
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
		yield 'Invalid query - pipe argument with space' => [
			[ '--query', '[[Category:Books]]| format=json', '--format', 'csv' ],
			'Query cannot contain pipe arguments aside from print requests, i.e. "|?Prop" is allowed,' .
				' but "|format=test" is not',
		];
		yield 'Invalid limit - non numeric' => [
			[ '--query', 'ignored', '--format', 'csv', '--limit', 'abc' ],
			'Limit must be a non-negative integer',
		];
		yield 'Invalid limit - decimal' => [
			[ '--query', 'ignored', '--format', 'csv', '--limit', '10.5' ],
			'Limit must be a non-negative integer',
		];
	}

	public static function provideValidQueries() {
		yield 'Print request' => [ '[[Category:Books]]|?Author' ];
		yield 'Value disjunction' => [ '[[Status::Active||Pending]]' ];
		yield 'Printout modifier' => [ '[[Status::Active]]|?Status|+filter=A' ];
	}

	/** @dataProvider provideValidQueries */
	public function testValidQueriesAreNotRejected( string $query ) {
		$mock = $this->createMock( SemanticReports::class );
		$mock->method( 'getReportData' )->willReturn( 'a,b' );

		$this->overrideMwServices();
		MediaWikiServices::getInstance()->redefineService(
			'SemanticReports',
			static function () use ( $mock ) {
				return $mock;
			}
		);
		$this->maintenance->loadWithArgv( [ '--query', $query, '--format', 'csv' ] );
		// Reaching the end of execute() without a RuntimeException proves the
		// guards accepted the query.
		$this->maintenance->execute();
		$this->expectNotToPerformAssertions();
	}
}
