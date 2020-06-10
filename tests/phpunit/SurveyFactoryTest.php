<?php

namespace Tests\QuickSurveys;

use Psr\Log\LoggerInterface;
use QuickSurveys\ExternalSurvey;
use QuickSurveys\InternalSurvey;
use QuickSurveys\SurveyAudience;
use QuickSurveys\SurveyFactory;

/**
 * @covers \QuickSurveys\SurveyFactory
 */
class SurveyFactoryTest extends \PHPUnit\Framework\TestCase {

	public function testItShouldThrowWhenThereIsNoQuestion() {
		SurveyFactory::factory(
			[
				'name' => 'test',
			],
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" survey doesn\'t have a question.' )
		);
	}

	public function testItShouldThrowWhenThereIsNoType() {
		SurveyFactory::factory(
			[
				'name' => 'test',
				'question' => 'Do you like writing unit tests?',
				'description' => 'A survey for (potential) developers on the QuickSurveys project.',
			],
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" survey isn\'t marked as internal or ' .
				'external.' )
		);
	}

	public function testItShouldThrowWhenThereAreNoPlatforms() {
		SurveyFactory::factory(
			[
				'name' => 'test',
				'type' => 'external',
				'question' => 'Do you like writing unit tests?',
				'description' => 'A survey for (potential) developers on the QuickSurveys project.',
				'coverage' => 1,
			],
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" survey doesn\'t have any platforms.' )
		);
	}

	public function testItShouldThrowWhenThereIsNoLink() {
		SurveyFactory::factory(
			[
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
			],
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" external survey doesn\'t have a link.' )
		);
	}

	public function testItShouldThrowWhenThereIsNoPrivacyPolicy() {
		SurveyFactory::factory(
			[
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
			],
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" external survey doesn\'t have a privacy ' .
				'policy.' )
		);
	}

	public function testItShouldThrowWhenAudienceConfigHasBadType() {
		SurveyFactory::factory(
			[
				'name' => 'test',
				'type' => 'internal',
				'question' => 'Do you like writing unit tests?',
				'enabled' => true,
				'coverage' => 1,
				'platforms' => [
					'desktop' => [
						'stable',
					],
				],
				'audience' => [
					'minEdits' => 'foobar',
				]
			],
			$this->expectsErrorLog(
				'Bad survey configuration: Bad value for parameter minEdits: must be a integer' )
		);
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
			new SurveyAudience( [ 'minEdits' => 100 ] ),
			'//example.org/test-external-survey',
			''
		);

		$actual = SurveyFactory::factory(
			[
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
				'audience' => [
					'minEdits' => 100,
				],
				'link' => '//example.org/test-external-survey',
				'privacyPolicy' => 'ext-quicksurveys-test-external-survey-privacy-policy',
				new SurveyAudience( [] )
			],
			$this->createMock( LoggerInterface::class )
		);

		$this->assertEquals( $actual, $expected );
	}

	public function testItShouldThrowWhenThereAreNoAnswers() {
		SurveyFactory::factory(
			[
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
			],
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" internal survey doesn\'t have any answers.' )
		);
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
			null,
			null
		);

		$actual = SurveyFactory::factory(
			[
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
			],
			$this->createMock( LoggerInterface::class )
		);

		$this->assertEquals( $actual, $expected );
	}

	public function testItShouldFactoryAnInternalSurveyWithShuffleAnswersDisplayDisabled() {
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
			false,
			null,
			null
		);

		$actual = SurveyFactory::factory(
			[
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
				'shuffleAnswersDisplay' => false,
			],
			$this->createMock( LoggerInterface::class )
		);

		$this->assertEquals( $actual, $expected );
	}

	public function testItShouldThrowIfTheTypeIsNotRecognized() {
		SurveyFactory::factory(
			[
				'name' => 'test',
				'type' => 'ixternal',
				'question' => 'Do you like writing unit tests?',
				'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
			],
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" survey isn\'t marked as internal or ' .
				'external.' )
		);
	}

	public function testItShouldMarkTheSurveyAsDisabledByDefault() {
		$survey = SurveyFactory::factory(
			[
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
			],
			$this->createMock( LoggerInterface::class )
		);

		$this->assertFalse( $survey->isEnabled() );
	}

	public function testItShouldThrowWhenThereIsNoCoverage() {
		SurveyFactory::factory(
			[
				'name' => 'test',
				'type' => 'internal',
				'question' => 'Do you like writing unit tests?',
				'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
			],
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" survey doesn\'t have a coverage.' )
		);
	}

	/**
	 * @dataProvider provideInvalidPlatforms
	 */
	public function testItShouldThrowWhenPlatformsIsInvalid( $platforms, $expectedMessage ) {
		$survey = SurveyFactory::factory(
			[
				'name' => 'test',
				'type' => 'internal',
				'question' => 'Do you like writing unit tests?',
				'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
				'coverage' => 1,
				'platforms' => $platforms,
			],
			$this->expectsErrorLog( 'Bad survey configuration: ' . $expectedMessage )
		);
		$this->assertNull( $survey );
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

	private function expectsErrorLog( string $message ) : LoggerInterface {
		$logger = $this->createMock( LoggerInterface::class );
		$logger
			->expects( $this->atLeastOnce() )
			->method( 'error' )
			->with(
				$this->equalTo( $message ),
				$this->anything()
			);
		return $logger;
	}
}
