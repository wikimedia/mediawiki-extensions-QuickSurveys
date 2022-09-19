<?php

namespace Tests\QuickSurveys;

use QuickSurveys\InternalSurvey;
use QuickSurveys\SurveyAudience;

/**
 * @covers \QuickSurveys\InternalSurvey
 * @covers \QuickSurveys\Survey
 */
class InternalSurveyTest extends \MediaWikiUnitTestCase {

	public function testBasicFunctionality() {
		$audience = new SurveyAudience( [] );
		$survey = new InternalSurvey(
			'name',
			'question',
			'description',
			true,
			0.5,
			[ 'desktop' ],
			'privacyPolicy',
			'additionalInfo',
			'confirmMsg',
			$audience,
			[ 'answer1' ],
			'shuffleAnswersDisplay',
			'freeformTextLabel',
			'embedElementId',
			'layout'
		);

		$this->assertSame( 'ext.quicksurveys.survey.name', $survey->getResourceLoaderModuleName() );
		$this->assertSame( $audience, $survey->getAudience() );
		$this->assertSame( [ 'question', 'description', 'privacyPolicy', 'additionalInfo',
			'confirmMsg', 'answer1', 'freeformTextLabel' ], $survey->getMessages() );
		$this->assertSame( [
			'audience' => [],
			'name' => 'name',
			'question' => 'question',
			'description' => 'description',
			'module' => 'ext.quicksurveys.survey.name',
			'coverage' => 0.5,
			'platforms' => [ 'desktop' ],
			'privacyPolicy' => 'privacyPolicy',
			'additionalInfo' => 'additionalInfo',
			'confirmMsg' => 'confirmMsg',
			'type' => 'internal',
			'answers' => [ 'answer1' ],
			'shuffleAnswersDisplay' => 'shuffleAnswersDisplay',
			'freeformTextLabel' => 'freeformTextLabel',
			'embedElementId' => 'embedElementId',
			'layout' => 'layout',
		], $survey->toArray() );
		$this->assertTrue( $survey->isEnabled() );
	}

}
