<?php

namespace QuickSurveys;

use Title;

/**
 * Determine whether any surveys will be shown on the current pageview.
 */
class SurveyContextFilter {
	/**
	 * The internal name of the view action (see \ViewAction) as returned by Action::getActionName.
	 */
	private const VIEW_ACTION_NAME = 'view';

	public static function isAnySurveyAvailable( ?Title $title, string $action ) {
		// The following tests are ordered from best to worst performance, with Title#isMainPage
		// and #exists being roughly tied. The best case for those two is a cache hit. The worst
		// case for Title#exists is a DB hit.
		return (
			$title
			&& $title->inNamespace( NS_MAIN )

			// Is the user viewing the page?
			&& $action === static::VIEW_ACTION_NAME

			&& !$title->isMainPage()
			&& $title->exists()
		);
	}
}
