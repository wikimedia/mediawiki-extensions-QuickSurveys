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

		// Do not allow surveys on main page.
		if ( $title->isMainPage() ) {
			return false;
		}

		// Check there is a survey available for this page.
		if ( !$this->isSurveyAvailableForPage( $title->getArticleID(), $title->getNamespace() ) ) {
			return false;
		}

		return $title->exists() || $title->isSpecialPage();
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
	 * @param int $namespace of page
	 *
	 * @return bool
	 */
	private function isSurveyAvailableForPage( int $pageId, int $namespace = 0 ): bool {
		foreach ( $this->surveys as $survey ) {
			$audience = $survey->getAudience()->toArray();
			$pageIds = $audience['pageIds'] ?? [];
			$validNamespaces = $audience['namespaces'] ?? [ 0 ];
			// If page IDs is defined check that the current page ID is in scope.
			if ( count( $pageIds ) > 0 && in_array( $pageId, $pageIds ) ) {
				return true;
			}
			// If no page IDs declared, is the survey valid for this namespace?
			if ( count( $pageIds ) === 0 && in_array( $namespace, $validNamespaces ) ) {
				return true;
			}
		}
		return false;
	}
}
