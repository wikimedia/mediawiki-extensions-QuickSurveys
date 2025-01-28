<?php

use MediaWiki\Config\Config;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use QuickSurveys\SurveyFactory;

return [
	'QuickSurveys.Config' =>
		/**
		 * Subset of configuration under the QuickSurveys namespace
		 */
		static function ( MediaWikiServices $services ): Config {
			return $services->getService( 'ConfigFactory' )
				->makeConfig( 'quicksurveys' );
		},
	'QuickSurveys.EnabledSurveys' =>
		/**
		 * @return \QuickSurveys\Survey[] List of active surveys to be selected from on the client
		 */
		static function ( MediaWikiServices $services ): array {
			$config = $services->getService( 'QuickSurveys.Config' );
			$configuredSurveys = $config->has( 'QuickSurveysConfig' )
				? $config->get( 'QuickSurveysConfig' )
				: [];
			$logger = LoggerFactory::getInstance( 'QuickSurveys' );

			$factory = new SurveyFactory( $logger );
			return $factory->parseSurveyConfig( $configuredSurveys );
		}
];
