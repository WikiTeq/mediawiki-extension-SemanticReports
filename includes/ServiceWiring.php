<?php

use MediaWiki\Extension\SemanticReports\SemanticReports;
use MediaWiki\MediaWikiServices;
use SMW\Services\ServicesFactory as ApplicationFactory;

/**
 * Service wiring for SemanticReports
 * @codeCoverageIgnore
 */
return [
	'SemanticReports' => static function ( MediaWikiServices $services ): SemanticReports {
		return new SemanticReports(
			ApplicationFactory::getInstance()->getQuerySourceFactory()
		);
	},
];
