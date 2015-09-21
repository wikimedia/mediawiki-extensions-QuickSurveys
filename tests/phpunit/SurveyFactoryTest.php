<?php

namespace Tests\QuickSurveys;

use PHPUnit_Framework_TestCase;
use QuickSurveys\SurveyFactory;
use QuickSurveys\InternalSurvey;
use QuickSurveys\ExternalSurvey;

class SurveyFactoryTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage The "test" survey doesn't have a question.
	 */
	public function testItShouldThrowWhenThereIsNoQuestion() {
		SurveyFactory::factory( array(
			'name' => 'test',
		) );
	}


	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage The "test" survey doesn't have a description.
	 */
	public function testItShouldThrowWhenThereIsNoDescription() {
		SurveyFactory::factory( array(
			'name' => 'test',
			'question' => 'Do you like writing unit tests?',
		) );
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage The "test" survey isn't marked as internal or external.
	 */
	public function testItShouldThrowWhenThereIsNoType() {
		SurveyFactory::factory( array(
			'name' => 'test',
			'question' => 'Do you like writing unit tests?',
			'description' => 'A survey for (potential) developers on the QuickSurveys project.',
		) );
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage The "test" external survey doesn't have a link.
	 */
	public function testItShouldThrowWhenThereIsNoLink() {
		SurveyFactory::factory( array(
			'name' => 'test',
			'type' => 'external',
			'question' => 'Do you like writing unit tests?',
			'description' => 'A survey for (potential) developers on the QuickSurveys project.',
			'coverage' => 1,
		) );
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage The "test" external survey doesn't have a privacy policy.
	 */
	public function testItShouldThrowWhenThereIsNoPrivacyPolicy() {
		SurveyFactory::factory( array(
			'name' => 'test',
			'type' => 'external',
			'question' => 'Do you like writing unit tests?',
			'description' => 'A survey for (potential) developers on the QuickSurveys project.',
			'coverage' => 1,
			'link' => '//example.org/test-external-survey',
		) );
	}

	public function testItShouldFactoryAnExternalSurvey() {
		$expected = new ExternalSurvey(
			'test',
			'Do you like writing unit tests?',
			'A survey for (potential) developers of the QuickSurveys extension.',
			true,
			1,
			'//example.org/test-external-survey',
			'ext-quicksurveys-test-external-survey-privacy-policy'
		);

		$actual = SurveyFactory::factory( array(
			'name' => 'test',
			'type' => 'external',
			'question' => 'Do you like writing unit tests?',
			'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
			'enabled' => true,
			'coverage' => 1,
			'link' => '//example.org/test-external-survey',
			'privacyPolicy' => 'ext-quicksurveys-test-external-survey-privacy-policy',
		) );

		$this->assertEquals( $actual, $expected );
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage The "test" internal survey doesn't have any answers.
	 */
	public function testItShouldThrowWhenThereAreNoAnswers() {
		SurveyFactory::factory( array(
			'name' => 'test',
			'type' => 'internal',
			'question' => 'Do you like writing unit tests?',
			'description' => 'A survey for (potential) developers on the QuickSurveys project.',
			'coverage' => 1,
		) );
	}

	public function testItShouldFactoryAnInternalSurvey() {
		$expected = new InternalSurvey(
			'test',
			'Do you like writing unit tests?',
			'A survey for (potential) developers of the QuickSurveys extension.',
			true,
			1,
			array(
				'ext-quicksurveys-test-internal-survey-positive',
			)
		);

		$actual = SurveyFactory::factory( array(
			'name' => 'test',
			'type' => 'internal',
			'question' => 'Do you like writing unit tests?',
			'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
			'enabled' => true,
			'coverage' => 1,
			'answers' => array(
				'ext-quicksurveys-test-internal-survey-positive',
			),
		) );

		$this->assertEquals( $actual, $expected );
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage The "test" survey isn't marked as internal or external.
	 */
	public function testItShouldThrowIfTheTypeIsNotRecognized() {
		SurveyFactory::factory( array(
			'name' => 'test',
			'type' => 'ixternal',
			'question' => 'Do you like writing unit tests?',
			'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
		) );
	}

	public function testItShouldMarkTheSurveyAsDisabledByDefault() {
		$survey = SurveyFactory::factory( array(
			'name' => 'test',
			'type' => 'internal',
			'question' => 'Do you like writing unit tests?',
			'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
			'coverage' => 1,
			'answers' => array(
				'ext-quicksurveys-test-internal-survey-positive',
			),
		) );

		$this->assertFalse( $survey->isEnabled() );
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage The "test" survey doesn't have a coverage.
	 */
	public function testItShouldThrowWhenThereIsNoCoverage() {
		SurveyFactory::factory( array(
			'name' => 'test',
			'type' => 'internal',
			'question' => 'Do you like writing unit tests?',
			'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
		) );
	}
}
