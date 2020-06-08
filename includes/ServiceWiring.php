<?php

use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use QuickSurveys\Survey;
use QuickSurveys\SurveyFactory;

return [
	'QuickSurveys.Config' => function ( MediaWikiServices $services ) {
		return $services->getService( 'ConfigFactory' )
			->makeConfig( 'quicksurveys' );
	},
	'QuickSurveys.EnabledSurveys' => function ( MediaWikiServices $services ) {
		$config = $services->getService( 'QuickSurveys.Config' );
		$configuredSurveys = $config->has( 'QuickSurveysConfig' )
			? $config->get( 'QuickSurveysConfig' )
			: [];
		$logger = LoggerFactory::getInstance( 'QuickSurveys' );
		$surveys = array_map(
			function ( array $spec ) use ( $logger ) {
				return SurveyFactory::factory( $spec, $logger );
			},
			$configuredSurveys
		);
		$enabledSurveys = array_filter( $surveys, function ( ?Survey $survey ) {
			return $survey && $survey->isEnabled();
		} );

		return array_values( $enabledSurveys );
	}
];
