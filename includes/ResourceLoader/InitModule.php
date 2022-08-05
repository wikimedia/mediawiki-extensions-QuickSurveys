<?php

namespace QuickSurveys\ResourceLoader;

use MediaWiki\MediaWikiServices;
use MediaWiki\ResourceLoader as RL;

/**
 * AKA The ext.quicksurveys.init module.
 *
 * By default, this module is empty. When there's at least one enabled survey defined then it
 * depends on the ext.quicksurveys.lib module and loads a script to show an available survey.
 * When there a no enabled surveys defined the majority of the frontend codebase won't be
 * fetched, parsed, and executed by the client.
 *
 * See T213459#4871107 for the original design of this approach.
 */
class InitModule extends RL\FileModule {
	public function __construct(
		array $options = [],
		string $localBasePath = null,
		string $remoteBasePath = null
	) {
		if ( $this->hasEnabledSurveys() ) {
			$options = array_merge( $options, [
				'packageFiles' => [
					'resources/ext.quicksurveys.init/init.js',
				],
			] );
		}
		parent::__construct( $options, $localBasePath, $remoteBasePath );
	}

	public function getDependencies( RL\Context $context = null ) {
		$result = parent::getDependencies( $context );

		if ( $this->hasEnabledSurveys() ) {
			$result = array_merge( $result, [ 'ext.quicksurveys.lib' ] );
		}

		return $result;
	}

	private function hasEnabledSurveys(): bool {
		$enabledSurveys = MediaWikiServices::getInstance()->getService( 'QuickSurveys.EnabledSurveys' );
		return count( $enabledSurveys ) > 0;
	}

	public function getTargets() {
		return [ 'desktop', 'mobile' ];
	}
}
