<?php

namespace Tests\QuickSurveys;

use MediaWiki\ResourceLoader\Context;
use QuickSurveys\ResourceLoader\InitModule;

/**
 * @covers \QuickSurveys\ResourceLoader\InitModule
 */
class InitModuleTest extends \MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		parent::setUp();
		$this->setService( 'QuickSurveys.EnabledSurveys', [ null ] );
	}

	public function testBasicFunctionality() {
		$context = $this->createMock( Context::class );
		$module = new InitModule( [], __DIR__ . '/../..' );

		$this->assertSame( [
			'main' => 'resources/ext.quicksurveys.init/init.js',
			'files' => [
				'resources/ext.quicksurveys.init/init.js' => [
					'type' => 'script',
					'content' => <<<'SCRIPT'
const forcedSurvey = mw.util.getParamValue( 'quicksurvey' );

require( 'ext.quicksurveys.lib' ).showSurvey( forcedSurvey );

SCRIPT
				]
			],
		], $module->getPackageFiles( $context ) );
		$this->assertSame( [ 'ext.quicksurveys.lib' ], $module->getDependencies() );
		$this->assertSame( [ 'desktop', 'mobile' ], $module->getTargets() );
	}

}
