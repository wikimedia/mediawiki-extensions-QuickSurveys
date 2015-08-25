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
		$vars['wgEnabledQuickSurveys'] = self::getEnabledSurveys();
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
		// Register enabled surveys as their own modules
		foreach ( $enabledSurveys as $survey ) {
			$messages = array();

			// All surveys have a question and description
			$messages[] = $survey['question'];
			$messages[] = $survey['description'];

			// Add messages that are specific the survey type
			if ( $survey['type'] === 'internal' ) {
				$messages[] = $survey['answers']['positive'];
				$messages[] = $survey['answers']['neutral'];
				$messages[] = $survey['answers']['negative'];

			} elseif ( $survey['type'] === 'external' ) {
				$messages[] = $survey['link'];
				// camelCase because the key will be a property of a JavaScript object.
				// Also this is not the key of the i18n message, it's the key that denotes the key of
				// the i18n message. Basically, key of a PHP array.
				$messages[] = $survey['privacyPolicy'];
			}

			$surveyModule = array( $survey['module'] => array(
				'messages' => $messages,
				'targets' => array( 'desktop', 'mobile' ),
			) );

			$resourceLoader->register( $surveyModule );
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
		// Get configured surveys
		$configuredSurveys = $config->has( 'QuickSurveysConfig' )
			? $config->get( 'QuickSurveysConfig' )
			: array();
		$enabledQuickSurveys = array();
		// Make enabled surveys available to the browser
		foreach ( $configuredSurveys as $survey ) {
			if ( $survey['enabled'] === true ) {
				$survey['module'] = self::getSurveyModuleName( $survey );
				$enabledQuickSurveys[] = $survey;
			}
		}
		return $enabledQuickSurveys;
	}

	/**
	 * Returns the name of the specified survey's module
	 *
	 * @return string Survey's ResourceLoader module name
	 */
	private static function getSurveyModuleName( $survey ) {
		return 'ext.quicksurveys.survey.' . str_replace( ' ', '.', $survey['name'] );
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
}
