<?php
/**
 * Hooks for QuickSurveys extension
 *
 * @file
 * @ingroup Extensions
 */

namespace QuickSurveys;

use ConfigFactory;
use OutputPage;
use ResourceLoader;
use Skin;

class Hooks {
	/**
	 * Register QUnit tests.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderTestModules
	 *
	 * @param array &$modules
	 * @param ResourceLoader &$rl
	 * @return bool
	 */
	public static function onResourceLoaderTestModules( &$modules, &$rl ) {
		$boilerplate = [
			'localBasePath' => __DIR__ . '/../tests/qunit/',
			'remoteExtPath' => 'QuickSurveys/tests/qunit',
			'targets' => [ 'desktop', 'mobile' ],
		];

		$modules['qunit']['ext.quicksurveys.lib.tests'] = $boilerplate + [
			'templates' => [
				'vector-1.html' => 'ext.quicksurveys.lib/templates/vector-1.html',
				'vector-2.html' => 'ext.quicksurveys.lib/templates/vector-2.html',
				'vector-3.html' => 'ext.quicksurveys.lib/templates/vector-3.html',
				'vector-4.html' => 'ext.quicksurveys.lib/templates/vector-4.html',
				'minerva-1.html' => 'ext.quicksurveys.lib/templates/minerva-1.html',
				'minerva-2.html' => 'ext.quicksurveys.lib/templates/minerva-2.html',
				'minerva-3.html' => 'ext.quicksurveys.lib/templates/minerva-3.html',
				'minerva-4.html' => 'ext.quicksurveys.lib/templates/minerva-4.html',
			],
			'scripts' => [
				'ext.quicksurveys.lib/test_lib.js',
			],
			'dependencies' => [
				'ext.quicksurveys.lib',
			],
		];
		return true;
	}

	/**
	 * ResourceLoaderGetConfigVars hook handler for registering enabled surveys
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderGetConfigVars
	 *
	 * @param array &$vars
	 * @return bool
	 */
	public static function onResourceLoaderGetConfigVars( &$vars ) {
		global $wgQuickSurveysRequireHttps;

		$surveys = self::getEnabledSurveys();
		$vars['wgQuickSurveysRequireHttps'] = $wgQuickSurveysRequireHttps;
		$vars['wgEnabledQuickSurveys'] = array_map( function ( Survey $survey ) {
			return $survey->toArray();
		}, $surveys );

		return true;
	}

	/**
	 * Init QuickSurveys in BeforePageDisplay hook on existing pages in the main namespace
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 *
	 * @param OutputPage &$out
	 * @param Skin &$sk
	 * @return bool
	 */
	public static function onBeforePageDisplay( OutputPage &$out, Skin &$sk ) {
		$title = $out->getTitle();
		if ( $title->inNamespace( NS_MAIN ) && $title->exists() ) {
			$out->addModules( 'ext.quicksurveys.init' );
		}
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
			$module = [
				$moduleName => [
					'messages' => $survey->getMessages(),
					'targets' => [ 'desktop', 'mobile' ],
				],
			];

			$resourceLoader->register( $module );
		}

		return true;
	}

	/**
	 * Helper method for getting enabled quick surveys
	 *
	 * @return Survey[] Enabled survey configuration array
	 */
	private static function getEnabledSurveys() {
		$config = ConfigFactory::getDefaultInstance()->makeConfig( 'quicksurveys' );
		$configuredSurveys = $config->has( 'QuickSurveysConfig' )
			? $config->get( 'QuickSurveysConfig' )
			: [];
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
	 * @param int[] &$schemas The schemas currently registered with the EventLogging
	 *  extension
	 * @return bool Always true
	 */
	public static function onEventLoggingRegisterSchemas( &$schemas ) {
		// @see https://meta.wikimedia.org/wiki/Schema:QuickSurveysResponses
		$schemas['QuickSurveysResponses'] = 15266417;
		$schemas['QuickSurveyInitiation'] = 15278946;

		return true;
	}
}
