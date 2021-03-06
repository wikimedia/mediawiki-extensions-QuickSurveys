<?php
/**
 * Hooks for QuickSurveys extension
 *
 * @file
 * @ingroup Extensions
 */

namespace QuickSurveys;

use Action;
use Config;
use MediaWiki\MediaWikiServices;
use OutputPage;
use ResourceLoader;
use ResourceLoaderContext;

class Hooks {

	/**
	 * ResourceLoaderGetConfigVars hook handler for registering enabled surveys
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderGetConfigVars
	 *
	 * @param array &$vars
	 */
	public static function onResourceLoaderGetConfigVars( &$vars ) {
		$surveys = MediaWikiServices::getInstance()->getService( 'QuickSurveys.EnabledSurveys' );

		$vars['wgEnabledQuickSurveys'] = array_map( static function ( Survey $survey ) {
			return $survey->toArray();
		}, $surveys );
	}

	/**
	 * Init QuickSurveys in BeforePageDisplay hook on existing pages in the main namespace
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 *
	 * @param OutputPage $out
	 */
	public static function onBeforePageDisplay( OutputPage $out ) {
		$context = $out->getContext();
		$title = $context->getTitle();
		$action = Action::getActionName( $context );
		$surveys = MediaWikiServices::getInstance()->getService( 'QuickSurveys.EnabledSurveys' );

		if ( ( new SurveyContextFilter( $surveys ) )->isAnySurveyAvailable( $title, $action ) ) {
			// TODO: It's annoying that we parse survey config a second time, inside this indirected
			//  call.  Ideally we could construct the ResourceLoader data module right here.
			$out->addModules( 'ext.quicksurveys.init' );
		}
	}

	/**
	 * @param ResourceLoaderContext $context
	 * @param Config $config
	 * @return array
	 */
	public static function getQuickSurveysConfig(
		ResourceLoaderContext $context,
		Config $config
	) {
		return [
			// Temporary feature module. When we remove the temporary QuickSurveysUseVue feature
			// flag, we will merge all the files into a single ResourceLoader module and make Vue.js
			// the default.
			'module' => $config->get( 'QuickSurveysUseVue' ) ?
				'ext.quicksurveys.lib.vue' : 'ext.quicksurveys.lib.views'
		];
	}

	/**
	 * ResourceLoaderRegisterModules hook handler
	 *
	 * Registers needed modules for enabled surveys
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderRegisterModules
	 *
	 * @param ResourceLoader $resourceLoader
	 */
	public static function onResourceLoaderRegisterModules( ResourceLoader $resourceLoader ) {
		$surveys = MediaWikiServices::getInstance()->getService( 'QuickSurveys.EnabledSurveys' );

		foreach ( $surveys as $survey ) {
			$moduleName = $survey->getResourceLoaderModuleName();
			$module = [
				$moduleName => [
					'messages' => $survey->getMessages(),
					'targets' => [ 'desktop', 'mobile' ],
				],
			];

			$resourceLoader->register( $module );
		}
	}
}
