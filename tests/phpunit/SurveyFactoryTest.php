<?php

namespace Tests\QuickSurveys;

use InvalidArgumentException;
use QuickSurveys\ExternalSurvey;
use QuickSurveys\InternalSurvey;
use QuickSurveys\SurveyAudience;
use QuickSurveys\SurveyFactory;

/**
 * @covers \QuickSurveys\SurveyFactory
 */
class SurveyFactoryTest extends \PHPUnit\Framework\TestCase {

	public function testItShouldThrowWhenThereIsNoQuestion() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'The "test" survey doesn\'t have a question.' );
		SurveyFactory::factory( [
			'name' => 'test',
		] );
	}

	public function testItShouldThrowWhenThereIsNoType() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'The "test" survey isn\'t marked as internal or external.' );
		SurveyFactory::factory( [
			'name' => 'test',
			'question' => 'Do you like writing unit tests?',
			'description' => 'A survey for (potential) developers on the QuickSurveys project.',
		] );
	}

	public function testItShouldThrowWhenThereAreNoPlatforms() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'The "test" survey doesn\'t have any platforms.' );
		SurveyFactory::factory( [
			'name' => 'test',
			'type' => 'external',
			'question' => 'Do you like writing unit tests?',
			'description' => 'A survey for (potential) developers on the QuickSurveys project.',
			'coverage' => 1,
		] );
	}

	public function testItShouldThrowWhenThereIsNoLink() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'The "test" external survey doesn\'t have a link.' );
		SurveyFactory::factory( [
			'name' => 'test',
			'type' => 'external',
			'question' => 'Do you like writing unit tests?',
			'description' => 'A survey for (potential) developers on the QuickSurveys project.',
			'coverage' => 1,
			'platforms' => [
				'desktop' => [
					'stable'
				],
				'mobile' => [
					'stable',
					'beta',
				],
			],
		] );
	}

	public function testItShouldThrowWhenThereIsNoPrivacyPolicy() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'The "test" external survey doesn\'t have a privacy policy.' );
		SurveyFactory::factory( [
			'name' => 'test',
			'type' => 'external',
			'question' => 'Do you like writing unit tests?',
			'description' => 'A survey for (potential) developers on the QuickSurveys project.',
			'coverage' => 1,
			'platforms' => [
				'desktop' => [
					'stable'
				],
				'mobile' => [
					'stable',
					'beta',
				],
			],
			'link' => '//example.org/test-external-survey',
		] );
	}

	public function testItShouldFactoryAnExternalSurvey() {
		$expected = new ExternalSurvey(
			'test',
			'Do you like writing unit tests?',
			'A survey for (potential) developers of the QuickSurveys extension.',
			true,
			1,
			[
				'desktop' => [
					'stable'
				],
				'mobile' => [
					'stable',
					'beta',
				],
			],
			'ext-quicksurveys-test-external-survey-privacy-policy',
			new SurveyAudience( [] ),
			'//example.org/test-external-survey',
			''
		);

		$actual = SurveyFactory::factory( [
			'name' => 'test',
			'type' => 'external',
			'question' => 'Do you like writing unit tests?',
			'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
			'enabled' => true,
			'coverage' => 1,
			'platforms' => [
				'desktop' => [
					'stable'
				],
				'mobile' => [
					'stable',
					'beta',
				],
			],
			'link' => '//example.org/test-external-survey',
			'privacyPolicy' => 'ext-quicksurveys-test-external-survey-privacy-policy',
			new SurveyAudience( [] )
		] );

		$this->assertEquals( $actual, $expected );
	}

	public function testItShouldThrowWhenThereAreNoAnswers() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'The "test" internal survey doesn\'t have any answers.' );
		SurveyFactory::factory( [
			'name' => 'test',
			'type' => 'internal',
			'question' => 'Do you like writing unit tests?',
			'description' => 'A survey for (potential) developers on the QuickSurveys project.',
			'coverage' => 1,
			'platforms' => [
				'desktop' => [
					'stable',
				],
				'mobile' => [
					'stable',
					'beta',
				],
			],
		] );
	}

	public function testItShouldFactoryAnInternalSurvey() {
		$expected = new InternalSurvey(
			'test',
			'Do you like writing unit tests?',
			'A survey for (potential) developers of the QuickSurveys extension.',
			true,
			1,
			[
				'desktop' => [
					'stable',
				],
				'mobile' => [
					'stable',
					'beta',
				],
			],
			'',
			new SurveyAudience( [] ),
			[
				'ext-quicksurveys-test-internal-survey-positive',
			],
			true,
			null
		);

		$actual = SurveyFactory::factory( [
			'name' => 'test',
			'type' => 'internal',
			'question' => 'Do you like writing unit tests?',
			'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
			'enabled' => true,
			'coverage' => 1,
			'platforms' => [
				'desktop' => [
					'stable',
				],
				'mobile' => [
					'stable',
					'beta',
				],
			],
			'answers' => [
				'ext-quicksurveys-test-internal-survey-positive',
			],
		] );

		$this->assertEquals( $actual, $expected );
	}

	public function testItShouldThrowIfTheTypeIsNotRecognized() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'The "test" survey isn\'t marked as internal or external.' );
		SurveyFactory::factory( [
			'name' => 'test',
			'type' => 'ixternal',
			'question' => 'Do you like writing unit tests?',
			'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
		] );
	}

	public function testItShouldMarkTheSurveyAsDisabledByDefault() {
		$survey = SurveyFactory::factory( [
			'name' => 'test',
			'type' => 'internal',
			'question' => 'Do you like writing unit tests?',
			'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
			'coverage' => 1,
			'platforms' => [
				'desktop' => [
					'stable',
				],
				'mobile' => [
					'stable',
					'beta',
				],
			],
			'answers' => [
				'ext-quicksurveys-test-internal-survey-positive',
			],
		] );

		$this->assertFalse( $survey->isEnabled() );
	}

	public function testItShouldThrowWhenThereIsNoCoverage() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'The "test" survey doesn\'t have a coverage.' );
		SurveyFactory::factory( [
			'name' => 'test',
			'type' => 'internal',
			'question' => 'Do you like writing unit tests?',
			'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
		] );
	}

	/**
	 * @dataProvider provideInvalidPlatforms
	 */
	public function testItShouldThrowWhenPlatformsIsInvalid( $platforms, $expectedMessage ) {
		try {
			SurveyFactory::factory( [
				'name' => 'test',
				'type' => 'internal',
				'question' => 'Do you like writing unit tests?',
				'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
				'coverage' => 1,
				'platforms' => $platforms,
			] );
		} catch ( InvalidArgumentException $e ) {
			$this->assertEquals( $expectedMessage, $e->getMessage() );
		}
	}

	public function provideInvalidPlatforms() {
		return [
			[
				[
					'desktop' => true,
				],
				'The "test" survey has specified an invalid platform. ' .
				'Please specify one or more of the following for the "desktop" platform: stable.'
			],
			[
				[
					'mobile' => [
						'stable',
						'alpha',
					],
				],
				'The "test" survey has specified an invalid platform. ' .
				'Please specify one or more of the following for the "mobile" platform: stable, beta.',
			],
		];
	}
}
