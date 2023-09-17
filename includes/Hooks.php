<?php
/**
 * Hooks for QuickSurveys extension
 *
 * @file
 * @ingroup Extensions
 */

namespace QuickSurveys;

use Config;
use MediaWiki\Hook\BeforePageDisplayHook;
use MediaWiki\MediaWikiServices;
use MediaWiki\ResourceLoader as RL;
use MediaWiki\ResourceLoader\Hook\ResourceLoaderRegisterModulesHook;
use MediaWiki\ResourceLoader\ResourceLoader;
use OutputPage;
use Skin;

class Hooks implements
	BeforePageDisplayHook,
	ResourceLoaderRegisterModulesHook
{

	/**
	 * Get data about the enabled surveys to be exported to the ext.quicksurveys.lib module
	 * via a virtual file.
	 *
	 * @param RL\Context $context
	 * @param Config $conf
	 * @return array[]
	 */
	public static function getSurveyConfig( RL\Context $context, Config $conf ) {
		$surveys = MediaWikiServices::getInstance()->getService( 'QuickSurveys.EnabledSurveys' );

		return array_map( static function ( Survey $survey ) {
			return $survey->toArray();
		}, $surveys );
	}

	/**
	 * Init QuickSurveys in BeforePageDisplay hook on existing pages in the main namespace
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		$title = $out->getTitle();
		$action = $out->getActionName();
		$surveys = MediaWikiServices::getInstance()->getService( 'QuickSurveys.EnabledSurveys' );

		if ( ( new SurveyContextFilter( $surveys ) )->isAnySurveyAvailable( $title, $action ) ) {
			// TODO: It's annoying that we parse survey config a second time, inside this indirected
			//  call.  Ideally we could construct the ResourceLoader data module right here.
			$out->addModules( 'ext.quicksurveys.init' );
		}
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
	public function onResourceLoaderRegisterModules( ResourceLoader $resourceLoader ): void {
		$surveys = MediaWikiServices::getInstance()->getService( 'QuickSurveys.EnabledSurveys' );

		foreach ( $surveys as $survey ) {
			$moduleName = $survey->getResourceLoaderModuleName();
			$module = [
				$moduleName => [
					'es6' => true,
					'dependencies' => [ 'ext.quicksurveys.lib.vue' ],
					'messages' => $survey->getMessages(),
				],
			];

			$resourceLoader->register( $module );
		}
	}
}
