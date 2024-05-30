<?php

namespace Tests\QuickSurveys;

use QuickSurveys\ExternalSurvey;
use QuickSurveys\SurveyAudience;
use QuickSurveys\SurveyQuestion;

/**
 * @covers \QuickSurveys\ExternalSurvey
 * @covers \QuickSurveys\Survey
 */
class ExternalSurveyTest extends \MediaWikiUnitTestCase {

	public function testBasicFunctionality() {
		$audience = new SurveyAudience( [] );
		$question = new SurveyQuestion( [
			'name' => 'question-1',
			'question' => 'question',
			'description' => 'description',
			'link' => 'link',
			'instanceTokenParameterName' => 'instanceTokenParameterName',
			'yesMsg' => 'yes',
			'noMsg' => 'no',
		], 'external' );
		$survey = new ExternalSurvey(
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
			'link',
			'instanceTokenParameterName',
			'yes',
			'no',
			'confirmDescription'
		);

		$this->assertSame( 'ext.quicksurveys.survey.name', $survey->getResourceLoaderModuleName() );
		$this->assertSame( $audience, $survey->getAudience() );
		$this->assertSame(
			[
				'question',
				'description',
				'privacyPolicy',
				'additionalInfo',
				'confirmMsg',
				'confirmDescription',
				// question, description, link, yes, and no should repeat again
				// because of keys in questions and in survey, just for testing
				'question',
				'description',
				'link',
				'yes',
				'no',
				'yes',
				'no',
				'link',
			],
			$survey->getMessages()
		);
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
					'question' => 'question',
					'description' => 'description',
					'link' => 'link',
					'instanceTokenParameterName' => 'instanceTokenParameterName',
					'yesMsg' => 'yes',
					'noMsg' => 'no',
				],
			],
			'confirmDescription' => 'confirmDescription',
			'type' => 'external',
			'link' => 'link',
			'instanceTokenParameterName' => 'instanceTokenParameterName',
			'yesMsg' => 'yes',
			'noMsg' => 'no',
		], $survey->toArray() );
	}

}
