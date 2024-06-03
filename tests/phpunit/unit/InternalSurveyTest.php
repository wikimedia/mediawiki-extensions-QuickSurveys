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
			0.5,
			[ 'desktop' ],
			'privacyPolicy',
			'additionalInfo',
			'confirmMsg',
			$audience,
			[ $question ],
			null,
			null,
			'confirmDescription',
			null,
			null,
			null,
			'embedElementId',
			null
		);

		$this->assertSame( 'ext.quicksurveys.survey.name', $survey->getResourceLoaderModuleName() );
		$this->assertSame( $audience, $survey->getAudience() );
		$this->assertSame(
			[
				'privacyPolicy',
				'additionalInfo',
				'confirmMsg',
				'confirmDescription',
				'question',
				'description',
				'answer1',
				'freeformTextLabel',
			],
			$survey->getMessages()
		);
		$this->assertSame( [
			'audience' => [],
			'name' => 'name',
			'question' => null,
			'description' => null,
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
			'answers' => null,
			'shuffleAnswersDisplay' => null,
			'freeformTextLabel' => null,
			'embedElementId' => 'embedElementId',
			'layout' => null
		], $survey->toArray() );
	}

}
