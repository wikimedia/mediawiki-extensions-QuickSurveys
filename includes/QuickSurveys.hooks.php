<?php
/**
 * Hooks for QuickSurveys extension
 *
 * @file
 * @ingroup Extensions
 */

namespace QuickSurveys;

use ConfigFactory;
use ResourceLoader;

class Hooks {
	/**
	 * ResourceLoaderGetConfigVars hook handler for registering enabled surveys
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderGetConfigVars
	 *
	 * @param array $vars
	 * @return boolean
	 */
	public static function onResourceLoaderGetConfigVars( &$vars ) {
		$surveys = self::getEnabledSurveys();
		$vars['wgEnabledQuickSurveys']= array_map( function ( Survey $survey ) {
			return $survey->toArray();
		}, $surveys );

		return true;
	}

	/**
	 * BeforePageDisplay hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 *
	 * @param OutputPage $out
	 * @param Skin $sk
	 * @return bool
	 */
	public static function onBeforePageDisplay( &$out, &$sk ) {
		$out->addModules( 'ext.quicksurveys.init' );
		return true;
	}

	/**
	 * ResourceLoaderRegisterModules hook handler
	 *
	 * Registers needed modules for enabled surveys
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderRegisterModules
	 *
	 * @param ResourceLoader &$resourceLoader The ResourceLoader object
	 * @return bool Always true
	 */
	public static function onResourceLoaderRegisterModules( ResourceLoader &$resourceLoader ) {
		$enabledSurveys = self::getEnabledSurveys();

		foreach ( $enabledSurveys as $survey ) {
			$moduleName = $survey->getResourceLoaderModuleName();
			$module = array(
				$moduleName => array(
					'messages' => $survey->getMessages(),
					'targets' => array( 'desktop', 'mobile' ),
				),
			);

			$resourceLoader->register( $module );
		}

		return true;
	}

	/**
	 * Helper method for getting enabled quick surveys
	 *
	 * @return array Enabled survey configuration array
	 */
	private static function getEnabledSurveys() {
		$config = ConfigFactory::getDefaultInstance()->makeConfig( 'quicksurveys' );
		$configuredSurveys = $config->has( 'QuickSurveysConfig' )
			? $config->get( 'QuickSurveysConfig' )
			: array();
		$surveys = array_map( '\\QuickSurveys\\SurveyFactory::factory', $configuredSurveys );
		$enabledSurveys = array_filter( $surveys, function ( Survey $survey ) {
			return $survey->isEnabled();
		} );

		return array_values( $enabledSurveys );
	}

	/**
	 * EventLoggingRegisterSchemas hook handler.
	 *
	 * Registers our EventLogging schemas so that they can be converted to
	 * ResourceLoaderSchemaModules by the EventLogging extension.
	 *
	 * If the module has already been registered in
	 * onResourceLoaderRegisterModules, then it is overwritten.
	 *
	 * @param array $schemas The schemas currently registered with the EventLogging
	 *  extension
	 * @return bool Always true
	 */
	public static function onEventLoggingRegisterSchemas( &$schemas ) {
		// @see https://meta.wikimedia.org/wiki/Schema:QuickSurveysResponses
		$schemas['QuickSurveysResponses'] = 13206704;

		return true;
	}

	/**
	 * UnitTestsList hook handler.
	 *
	 * Adds the path to the QuickSurveys PHPUnit tests to the set of enabled
	 * extension's test suites.
	 *
	 * @param array $paths The set of paths to other extension's PHPUnit test
	 *  suites
	 * @return bool Always true
	 */
	public static function onUnitTestsList( array &$paths ) {
		$paths[] = __DIR__ . '/../tests/phpunit';

		return true;
	}
}
