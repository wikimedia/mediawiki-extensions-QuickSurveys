<?php
namespace QuickSurveys;

use ResourceLoaderFileModule;
use ResourceLoaderContext;

/**
 * A ResourceLoader module that serves Hogan or Mustache depending on the current target.
 * All muhogan modules must be compatible with both Hogan and Mustache templating languages.
 */
class ResourceLoaderMuHoganModule extends ResourceLoaderFileModule {
	/**
	 * @inheritdoc
	 */
	public function getDependencies( ResourceLoaderContext $context = null ) {
		$deps = parent::getDependencies( $context );
		if ( $context->getRequest()->getVal( 'target' ) === 'mobile' ) {
			$deps[] = 'mediawiki.template.hogan';
		} else {
			$deps[] = 'mediawiki.template.mustache';
		}
		return $deps;
	}
}
