<?php
/**
 * Hooks for QuickSurveys extension
 *
 * @file
 * @ingroup Extensions
 */

namespace QuickSurveys;

use Action;
use MediaWiki\MediaWikiServices;
use OutputPage;
use ResourceLoader;

class Hooks {

	/**
	 * The internal name of the view action (see \ViewAction) as returned by Action::getActionName.
	 */
	private const VIEW_ACTION_NAME = 'view';

	/**
	 * ResourceLoaderGetConfigVars hook handler for registering enabled surveys
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderGetConfigVars
	 *
	 * @param array &$vars
	 */
	public static function onResourceLoaderGetConfigVars( &$vars ) {
		$surveys = MediaWikiServices::getInstance()->getService( 'QuickSurveys.EnabledSurveys' );

		$vars['wgEnabledQuickSurveys'] = array_map( function ( Survey $survey ) {
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

		// The following tests are ordered from best to worst performance, with Title#isMainPage
		// and #exists being roughly tied. The best case for those two is a cache hit. The worst
		// case for Title#exists is a DB hit.
		if (
			$title
			&& $title->inNamespace( NS_MAIN )

			// Is the user viewing the page?
			&& Action::getActionName( $context ) === static::VIEW_ACTION_NAME

			&& !$title->isMainPage()
			&& $title->exists()
		) {
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
