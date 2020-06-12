<?php

use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use QuickSurveys\SurveyFactory;

return [
	'QuickSurveys.Config' =>
		/**
		 * Subset of configuration under the QuickSurveys namespace
		 */
		function ( MediaWikiServices $services ) : Config {
			return $services->getService( 'ConfigFactory' )
				->makeConfig( 'quicksurveys' );
		},
	'QuickSurveys.EnabledSurveys' =>
		/**
		 * @param MediaWikiServices $services
		 * @return \QuickSurveys\Survey[] List of active surveys to be selected from on the client
		 */
		function ( MediaWikiServices $services ) : array {
			$config = $services->getService( 'QuickSurveys.Config' );
			$configuredSurveys = $config->has( 'QuickSurveysConfig' )
				? $config->get( 'QuickSurveysConfig' )
				: [];
			$logger = LoggerFactory::getInstance( 'QuickSurveys' );

			$factory = new SurveyFactory( $logger );
			return $factory->parseSurveyConfig( $configuredSurveys );
		}
];
