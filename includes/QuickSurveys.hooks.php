<?php
/**
 * Hooks for QuickSurveys extension
 *
 * @file
 * @ingroup Extensions
 */

namespace QuickSurveys;

use ConfigFactory;

class Hooks {
	/**
	 * ResourceLoaderGetConfigVars hook handler for registering enabled surveys
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderGetConfigVars
	 *
	 * @param array $vars
	 * @return boolean
	 */
	public static function onResourceLoaderGetConfigVars( &$vars ) {
		$config = ConfigFactory::getDefaultInstance()->makeConfig( 'quicksurveys' );
		// Get configured surveys
		$configuredSurveys = $config->has( 'QuickSurveysConfig' )
			? $config->get( 'QuickSurveysConfig' )
			: array();
		$enabledQuickSurveys = array();
		// Make enabled surveys available to the browser
		foreach ( $configuredSurveys as $survey ) {
			if ( $survey['enabled'] === true ) {
				$enabledQuickSurveys[] = $survey;
			}
		}
		$vars['wgEnabledQuickSurveys'] = $enabledQuickSurveys;
		return true;
	}
}
