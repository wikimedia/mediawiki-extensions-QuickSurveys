<?php

namespace Tests\QuickSurveys;

use MediaWiki\Title\Title;
use QuickSurveys\Survey;
use QuickSurveys\SurveyAudience;
use QuickSurveys\SurveyContextFilter;

/**
 * @group Database
 * @covers \QuickSurveys\SurveyContextFilter
 */
class SurveyContextFilterTest extends \MediaWikiIntegrationTestCase {

	public function setUp(): void {
		parent::setUp();

		$this->getExistingTestPage( 'Foo' );
		$this->getExistingTestPage( 'Talk:Foo' );
		$this->getExistingTestPage( 'User:Foo' );
		$this->getExistingTestPage( 'User:Bar' );
		$this->getNonexistingTestPage( 'Nonexistent' );
	}

	/**
	 * @dataProvider contextProvider
	 */
	public function testIsAnySurveyAvailable(
		?string $titleText,
		string $action,
		?string $targeting,
		bool $expected
	) {
		$title = Title::newFromText( $titleText );
		$survey = $this->createMock( Survey::class );
		if ( $targeting ) {
			$targetingTitle = Title::newFromText( $targeting );
			$audience = $this->createMock( SurveyAudience::class );
			$audience->method( 'toArray' )->willReturn(
				[ 'pageIds' => [ $targetingTitle->getArticleID() ] ] );
			$survey->method( 'getAudience' )->willReturn( $audience );
		}

		$filter = new SurveyContextFilter( [ $survey ] );
		$result = $filter->isAnySurveyAvailable( $title, $action );

		$this->assertSame( $expected, $result );
	}

	public static function contextProvider() {
		return [
			'No title' =>
				[ null, 'view', null, false ],
			'Title in Talk namespace' =>
				[ 'Talk:Foo', 'view', null, false ],
			'Edit action' =>
				[ 'Foo', 'edit', null, false ],
			'Main Page' =>
				[ 'Main Page', 'view', null, false ],
			'Nonexistent article' =>
				[ 'Nonexistent', 'view', null, false ],
			'Main namespace article' =>
				[ 'Foo', 'view', null, true ],
			'Main namespace article, targeting does not interfere' =>
				[ 'Foo', 'view', 'Project:Foo', true ],
			'Non-Main namespace, not targeted page' =>
				[ 'User:Foo', 'view', 'User:Bar', false ],
			'Non-Main namspace, is targeted page' =>
				[ 'User:Bar', 'view', 'User:Bar', true ],
		];
	}

	public function testIsAnySurveyAvailable_empty() {
		$filter = new SurveyContextFilter( [] );
		$result = $filter->isAnySurveyAvailable( Title::newFromText( 'Foo' ), 'view' );
		$this->assertFalse( $result );
	}
}
