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
			$hookContainer = $services->getHookContainer();
			$config = $services->getService( 'QuickSurveys.Config' );
			$configuredSurveys = $config->has( 'QuickSurveysConfig' )
				? $config->get( 'QuickSurveysConfig' )
				: [];
			$logger = LoggerFactory::getInstance( 'QuickSurveys' );
			// The hook allows addition of surveys. You cannot remove surveys defined by configuration.
			$hookSurveys = [];
			$hookContainer->run( 'QuickSurveysEnabled', [ &$hookSurveys ], [] );
			// Iterate through the all surveys and make sure they all have unique
			// names. Where they do the configured survey overrides the hook survey.
			$uniqueSurveys = [];
			foreach ( array_merge( $hookSurveys, $configuredSurveys ) as $survey ) {
				$uniqueSurveys[ $survey['name'] ] = $survey;
			}
			$factory = new SurveyFactory( $logger );
			return $factory->parseSurveyConfig( array_values( $uniqueSurveys ) );
		}
];
