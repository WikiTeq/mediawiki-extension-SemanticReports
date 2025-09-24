<?php

namespace MediaWiki\Extension\SemanticReports;

use SMW\MediaWiki\Api\ApiRequestParameterFormatter;
use SMW\Query\QueryContext;
use SMW\Query\QueryResult;
use SMW\Query\QuerySourceFactory;
use SMW\Query\ResultPrinters\CsvFileExportPrinter;
use SMWQuery;
use SMWQueryProcessor;

class SemanticReports {

	private QuerySourceFactory $querySourceFactory;

	public function __construct(
		QuerySourceFactory $querySourceFactory
	) {
		$this->querySourceFactory = $querySourceFactory;
	}

	/**
	 * Retrieves report data based on the provided query, processes it,
	 * and returns the result as a CSV formatted string.
	 *
	 * @param string $query The query string to fetch and process data.
	 *
	 * @return string|false The CSV formatted result as a string, or false if there are errors.
	 */
	public function getReportData( string $query, string $format ) {
		// append query defaults
		$suffix =
			'|format=' . $format .
			'|limit=1000000000' .
			'|mainlabel=Title' .
			'|link=none' .
			'|valuesep=;' .
			'|sep=,' .
			'|showsep=no' .
			'|bom=no' .
			'|merge=no';
		$query .= ' ' . $suffix;

		// get the query components
		$parameterFormatter = new ApiRequestParameterFormatter( [ 'query' => $query ] );
		[ $queryString, $parameters, $printouts ] = SMWQueryProcessor::getComponentsFromFunctionParams(
			$parameterFormatter->getAskApiParameters(),
			false
		);

		// process the parameters for printer
		$processedParams = SMWQueryProcessor::getProcessedParams(
			$parameters,
			$printouts,
			true,
			QueryContext::INLINE_QUERY,
			false
		);

		// run the query
		$queryResult = $this->getQueryResult( $this->getQuery(
			$queryString,
			$printouts,
			$parameters,
			$format
		) );

		// return false if there are errors
		/** @phan-suppress-next-line PhanUndeclaredClassMethod */
		if ( $queryResult->getErrors() !== [] ) {
			return false;
		}

		// TODO: for now we support ONLY CSV format!
		$printer = new CsvFileExportPrinter( 'csv' );

		return $printer->getResult(
			$queryResult,
			$processedParams,
			SMW_OUTPUT_FILE
		);
	}

	/**
	 * Returns a query object for the provided query string and list of printouts.
	 *
	 * @param string $queryString
	 * @param array $printouts
	 * @param array $parameters
	 * @param string $format
	 *
	 * @return SMWQuery
	 */
	private function getQuery(
		string $queryString,
		array $printouts,
		array $parameters = [],
		string $format = ''
	): SMWQuery {
		SMWQueryProcessor::addThisPrintout( $printouts, $parameters );

		$query = SMWQueryProcessor::createQuery(
			$queryString,
			SMWQueryProcessor::getProcessedParams( $parameters, $printouts ),
			QueryContext::SPECIAL_PAGE,
			$format,
			$printouts
		);

		// we do not want the query result to be cached so we intentionally omit setting the context
		// $query->setOption( SMWQuery::PROC_CONTEXT, 'SemanticReports' );

		return $query;
	}

	/**
	 * Run the actual query and return the result.
	 *
	 * @param SMWQuery $query
	 *
	 * @phan-suppress PhanUndeclaredTypeReturnType
	 * @return QueryResult|\SMWQueryResult
	 */
	private function getQueryResult( SMWQuery $query ) {
		return $this->querySourceFactory
			->get( $query->getQuerySource() )
			->getQueryResult( $query );
	}

}
