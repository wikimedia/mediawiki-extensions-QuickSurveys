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
	 * Register QUnit tests.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderTestModules
	 *
	 * @param array $files
	 * @return bool
	 */
	public static function onResourceLoaderTestModules( &$modules, &$rl ) {
		$boilerplate = array(
			'localBasePath' => __DIR__ . '/../tests/qunit/',
			'remoteExtPath' => 'QuickSurveys/tests/qunit',
			'targets' => array( 'desktop', 'mobile' ),
		);

		$modules['qunit']['ext.quicksurveys.lib.tests'] = $boilerplate + array(
			'templates' => array(
				'vector-1.html' => 'ext.quicksurveys.lib/templates/vector-1.html',
				'vector-2.html' => 'ext.quicksurveys.lib/templates/vector-2.html',
				'vector-3.html' => 'ext.quicksurveys.lib/templates/vector-3.html',
				'vector-4.html' => 'ext.quicksurveys.lib/templates/vector-4.html',
				'minerva-1.html' => 'ext.quicksurveys.lib/templates/minerva-1.html',
				'minerva-2.html' => 'ext.quicksurveys.lib/templates/minerva-2.html',
				'minerva-3.html' => 'ext.quicksurveys.lib/templates/minerva-3.html',
				'minerva-4.html' => 'ext.quicksurveys.lib/templates/minerva-4.html',
			),
			'scripts' => array(
				'ext.quicksurveys.lib/test_lib.js',
			),
			'dependencies' => array(
				'ext.quicksurveys.lib',
			),
		);
		return true;
	}

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
	 * Init QuickSurveys in BeforePageDisplay hook on existing pages in the main namespace
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 *
	 * @param OutputPage $out
	 * @param Skin $sk
	 * @return bool
	 */
	public static function onBeforePageDisplay( &$out, &$sk ) {
		$title = $out->getTitle();
		if ( $title->inNamespace( NS_MAIN ) && $title->exists() ) {
			$out->addModules( 'ext.quicksurveys.init' );
		}
		return true;
	}

	/**
	 * Extension function to report when EventLogging is not installed
	 */
	public static function onExtensionSetup() {
		if ( !class_exists( 'EventLogging' ) ) {
			echo "QuickSurveys extension requires EventLogging.\n";
			die( -1 );
		}
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
		$schemas['QuickSurveysResponses'] = 14136037;

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
