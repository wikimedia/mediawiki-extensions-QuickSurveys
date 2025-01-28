<?php
/**
 * Hooks for QuickSurveys extension
 *
 * @file
 * @ingroup Extensions
 */

namespace QuickSurveys;

use MediaWiki\Config\Config;
use MediaWiki\MediaWikiServices;
use MediaWiki\Output\Hook\BeforePageDisplayHook;
use MediaWiki\Output\OutputPage;
use MediaWiki\ResourceLoader as RL;
use MediaWiki\ResourceLoader\Hook\ResourceLoaderRegisterModulesHook;
use MediaWiki\ResourceLoader\ResourceLoader;
use MediaWiki\Session\SessionManager;
use MediaWiki\Skin\Skin;
use MediaWiki\User\User;

class Hooks implements
	BeforePageDisplayHook,
	ResourceLoaderRegisterModulesHook
{

	/**
	 * Get data about the enabled surveys to be exported to the ext.quicksurveys.lib module
	 * via a virtual file.
	 *
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
		$contextFilter = new SurveyContextFilter( $surveys );

		if ( $contextFilter->isAnySurveyAvailable( $title, $action ) ) {
			$needsFirstEdit = $contextFilter->isAudienceKeySet( 'firstEdit' );
			$needsLastEdit = $contextFilter->isAudienceKeySet( 'lastEdit' );

			// Only pass the user's first and last edit dates if a couple of
			// conditions are met.
			//
			// First, there must be an available survey that requires one of
			// these. Secondly, we should only query for them if the user has
			// any edits in the first place. Fetching the total count is much
			// faster than finding the timestamps of specific edits.
			if ( $needsFirstEdit || $needsLastEdit ) {
				$userEditTracker = MediaWikiServices::getInstance()->getUserEditTracker();
				$user = SessionManager::getGlobalSession()->getUser();

				if ( $userEditTracker->getUserEditCount( $user ) > 0 ) {
					if ( $needsFirstEdit ) {
						$firstEditTimestamp = $userEditTracker->getFirstEditTimestamp( $user );
						$out->addJsConfigVars( [
							'wgQSUserFirstEditDate' =>
								$firstEditTimestamp ? $this->formatDate( $firstEditTimestamp ) : null,
						] );
					}

					if ( $needsLastEdit ) {
						$lastEditTimestamp = $userEditTracker->getLatestEditTimestamp( $user );
						$out->addJsConfigVars( [
							'wgQSUserLastEditDate' =>
								$lastEditTimestamp ? $this->formatDate( $lastEditTimestamp ) : null,
						] );
					}
				}
			}

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
	 */
	public function onResourceLoaderRegisterModules( ResourceLoader $resourceLoader ): void {
		$surveys = MediaWikiServices::getInstance()->getService( 'QuickSurveys.EnabledSurveys' );

		foreach ( $surveys as $survey ) {
			$moduleName = $survey->getResourceLoaderModuleName();
			$module = [
				$moduleName => [
					'dependencies' => [ 'ext.quicksurveys.lib.vue' ],
					'messages' => $survey->getMessages(),
				],
			];

			$resourceLoader->register( $module );
		}
	}

	/**
	 * Adds a default-enabled preference to gate the feature
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/GetPreferences
	 */
	public static function onGetPreferences( User $user, array &$prefs ): void {
		$prefs['displayquicksurveys'] = [
			'type' => 'select',
			'section' => 'personal/quicksurveyext',
			'label-message' => 'ext-quicksurveys-pref-displayquicksurveys-label',
			'help-message' => 'ext-quicksurveys-pref-displayquicksurveys-help',
			'options' => [
				(string)wfMessage( 'ext-quicksurveys-pref-displayquicksurveys-option-enabled' ) => 1,
				(string)wfMessage( 'ext-quicksurveys-pref-displayquicksurveys-option-disabled' ) => 0,
			],
		];
	}

	/**
	 * Convert a string timestamp with format (YYYYMMDDHHSS) to (YY-MM-DD)
	 */
	private function formatDate( string $timestamp ): string {
		$year = substr( $timestamp, 0, 4 );
		$month = substr( $timestamp, 4, 2 );
		$day = substr( $timestamp, 6, 2 );

		return "{$year}-{$month}-{$day}";
	}
}
