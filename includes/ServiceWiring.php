<?php

use MediaWiki\MediaWikiServices;
use QuickSurveys\Survey;

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
		$surveys = array_map( '\\QuickSurveys\\SurveyFactory::factory', $configuredSurveys );
		$enabledSurveys = array_filter( $surveys, function ( Survey $survey ) {
			return $survey->isEnabled();
		} );

		return array_values( $enabledSurveys );
	}
];
