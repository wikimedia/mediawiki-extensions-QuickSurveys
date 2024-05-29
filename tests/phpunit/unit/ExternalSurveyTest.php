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
				'link',
				'yes',
				'no',
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
			'link' => null,
			'instanceTokenParameterName' => null,
			'yesMsg' => null,
			'noMsg' => null,
		], $survey->toArray() );
	}

}
