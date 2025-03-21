<?php

namespace QuickSurveys;

use MediaWiki\Title\Title;

/**
 * Determine whether any surveys will be shown on the current pageview.
 */
class SurveyContextFilter {
	/**
	 * The internal name of the view action (see \ViewAction) as returned by ActionFactory::getActionName.
	 */
	private const VIEW_ACTION_NAME = 'view';

	/**
	 * @var Survey[]
	 */
	private array $surveys;

	/**
	 * @param Survey[] $surveys
	 */
	public function __construct( array $surveys ) {
		$this->surveys = $surveys;
	}

	/**
	 * @param Title|null $title
	 * @param string $action
	 *
	 * @return bool
	 */
	public function isAnySurveyAvailable( ?Title $title, string $action ): bool {
		if ( !$this->surveys ) {
			return false;
		}

		if ( $title === null || $action !== static::VIEW_ACTION_NAME ) {
			return false;
		}

		// Typically disabled outside of the main namespace, as well as on the main page
		if ( !$title->inNamespace( NS_MAIN ) || $title->isMainPage() ) {
			// Allow surveys to target specific pages regardless of namespace.
			if ( !$this->isKnownPageId( $title->getArticleID() ) ) {
				return false;
			}
		}

		return $title->exists();
	}

	/**
	 * Checks if a specific audience key is set for any surveys.
	 *
	 * @param string $key
	 * @return bool
	 */
	public function isAudienceKeySet( string $key ): bool {
		foreach ( $this->surveys as $survey ) {
			$audience = $survey->getAudience()->toArray();
			if ( isset( $audience[$key] ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param int $pageId
	 *
	 * @return bool
	 */
	private function isKnownPageId( int $pageId ): bool {
		foreach ( $this->surveys as $survey ) {
			$audience = $survey->getAudience()->toArray();
			if ( in_array( $pageId, $audience['pageIds'] ?? [] ) ) {
				return true;
			}
		}
		return false;
	}
}
