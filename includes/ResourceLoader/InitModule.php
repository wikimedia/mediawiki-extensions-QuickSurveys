<?php

namespace QuickSurveys\ResourceLoader;

use MediaWiki\MediaWikiServices;
use MediaWiki\ResourceLoader as RL;

/**
 * AKA The ext.quicksurveys.init module.
 *
 * By default, this module is empty. When there's at least one enabled survey defined then it
 * depends on the ext.quicksurveys.lib module. That is, when there a no enabled surveys defined
 * the majority of the frontend codebase won't be fetched, parsed, and executed by the client.
 *
 * See T213459#4871107 for the original design of this approach.
 */
class InitModule extends RL\FileModule {
	public function getDependencies( RL\Context $context = null ) {
		$enabledSurveys = MediaWikiServices::getInstance()->getService( 'QuickSurveys.EnabledSurveys' );

		$result = parent::getDependencies( $context );

		if ( count( $enabledSurveys ) > 0 ) {
			$result = array_merge( $result, [ 'ext.quicksurveys.lib' ] );
		}

		return $result;
	}

	public function getTargets() {
		return [ 'desktop', 'mobile' ];
	}
}
