<?php

namespace Tests\QuickSurveys;

use QuickSurveys\SurveyContextFilter;
use Title;

/**
 * @group Database
 * @covers \QuickSurveys\SurveyContextFilter
 */
class SurveyContextFilterTest extends \MediaWikiIntegrationTestCase {
	private $existingPage;
	private $existingTalkPage;
	private $nonexistingPage;

	public function setUp() : void {
		parent::setUp();

		$this->getExistingTestPage( 'Foo' );
		$this->getExistingTestPage( 'Talk:Foo' );
		$this->getNonexistingTestPage( 'Nonexistent' );
	}

	/**
	 * @dataProvider contextProvider
	 */
	public function testIsAnySurveyAvailable( ?string $titleText, string $action, bool $expected ) {
		$title = Title::newFromText( $titleText );
		$result = SurveyContextFilter::isAnySurveyAvailable( $title, $action );
		$this->assertSame( $expected, $result );
	}

	public function contextProvider() {
		return [
			'No title' => [ null, 'view', false ],
			'Title in Talk namespace' => [ 'Talk:Foo', 'view', false ],
			'Edit action' => [ 'Foo', 'edit', false ],
			'Main Page' => [ 'Main Page', 'view', false ],
			'Nonexistent article' => [ 'Nonexistent', 'view', false ],
			'Main namespace article' => [ 'Foo', 'view', true ],
		];
	}
}
