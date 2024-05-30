<?php

namespace Tests\QuickSurveys;

use QuickSurveys\InternalSurvey;
use QuickSurveys\SurveyAudience;
use QuickSurveys\SurveyQuestion;

/**
 * @covers \QuickSurveys\InternalSurvey
 * @covers \QuickSurveys\Survey
 */
class InternalSurveyTest extends \MediaWikiUnitTestCase {

	public function testBasicFunctionality() {
		$audience = new SurveyAudience( [] );
		$question = new SurveyQuestion( [
			'name' => 'question-1',
			'layout' => 'layout',
			'question' => 'question',
			'description' => 'description',
			'shuffleAnswersDisplay' => false,
			'answers' => [
				[
					'label' => 'answer1',
					'freeformTextLabel' => 'freeformTextLabel',
				],
			],
		], 'internal' );
		$survey = new InternalSurvey(
			'name',
			'question',
			'description',
			0.5,
			[ 'desktop' ],
			'privacyPolicy',
			'additionalInfo',
			'confirmMsg',
			$audience,
			[ $question ],
			[ 'answer1' ],
			false,
			'freeformTextLabel',
			'embedElementId',
			'layout',
			'confirmDescription'
		);

		$this->assertSame( 'ext.quicksurveys.survey.name', $survey->getResourceLoaderModuleName() );
		$this->assertSame( $audience, $survey->getAudience() );
		$this->assertSame( [
			'question',
			'description',
			'privacyPolicy',
			'additionalInfo',
			'confirmMsg',
			'confirmDescription',
			// question, description, answer1, and freeformTextLabel should repeat again
			// because of keys in questions and in survey, just for testing
			'question',
			'description',
			'answer1',
			'freeformTextLabel',
			'answer1',
			'freeformTextLabel' ], $survey->getMessages() );
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
			'questions' => [
				[
					'name' => 'question-1',
					'layout' => 'layout',
					'question' => 'question',
					'description' => 'description',
					'shuffleAnswersDisplay' => false,
					'answers' => [
						[
							'label' => 'answer1',
							'freeformTextLabel' => 'freeformTextLabel',
						],
					],
				],
			],
			'confirmDescription' => 'confirmDescription',
			'type' => 'internal',
			'answers' => [ 'answer1' ],
			'shuffleAnswersDisplay' => false,
			'freeformTextLabel' => 'freeformTextLabel',
			'embedElementId' => 'embedElementId',
			'layout' => 'layout',
		], $survey->toArray() );
	}

}
