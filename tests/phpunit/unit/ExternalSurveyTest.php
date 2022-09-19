<?php

namespace Tests\QuickSurveys;

use QuickSurveys\ExternalSurvey;
use QuickSurveys\SurveyAudience;

/**
 * @covers \QuickSurveys\ExternalSurvey
 * @covers \QuickSurveys\Survey
 */
class ExternalSurveyTest extends \MediaWikiUnitTestCase {

	public function testBasicFunctionality() {
		$audience = new SurveyAudience( [] );
		$survey = new ExternalSurvey(
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
			'link',
			'instanceTokenParameterName'
		);

		$this->assertSame( 'ext.quicksurveys.survey.name', $survey->getResourceLoaderModuleName() );
		$this->assertSame( $audience, $survey->getAudience() );
		$this->assertSame( [ 'question', 'description', 'privacyPolicy', 'additionalInfo',
			'confirmMsg', 'link' ], $survey->getMessages() );
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
			'type' => 'external',
			'link' => 'link',
			'instanceTokenParameterName' => 'instanceTokenParameterName',
		], $survey->toArray() );
		$this->assertTrue( $survey->isEnabled() );
	}

}
