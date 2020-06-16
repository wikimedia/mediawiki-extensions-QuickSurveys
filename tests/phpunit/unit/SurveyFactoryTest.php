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
class SurveyFactoryTest extends \MediaWikiUnitTestCase {

	public function testItShouldThrowWhenThereIsNoQuestion() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" survey doesn\'t have a question.' )
		);
		$factory->newSurvey(
			[
				'name' => 'test',
			]
		);
	}

	public function testItShouldThrowWhenThereIsNoType() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" survey isn\'t marked as internal or ' .
				'external.' )
		);
		$factory->newSurvey(
			[
				'name' => 'test',
				'question' => 'Do you like writing unit tests?',
				'description' => 'A survey for (potential) developers on the QuickSurveys project.',
			]
		);
	}

	public function testItShouldThrowWhenThereAreNoPlatforms() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" survey doesn\'t have any platforms.' )
		);
		$factory->newSurvey(
			[
				'name' => 'test',
				'type' => 'external',
				'question' => 'Do you like writing unit tests?',
				'description' => 'A survey for (potential) developers on the QuickSurveys project.',
				'coverage' => 1,
			]
		);
	}

	public function testItShouldThrowWhenThereIsNoLink() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" external survey doesn\'t have a link.' )
		);
		$factory->newSurvey(
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
			]
		);
	}

	public function testItShouldThrowWhenThereIsNoPrivacyPolicy() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" external survey doesn\'t have a privacy ' .
				'policy.' )
		);
		$factory->newSurvey(
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
			]
		);
	}

	public function testItShouldThrowWhenAudienceConfigHasBadType() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: Bad value for parameter minEdits: must be a integer' )
		);
		$factory->newSurvey(
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
				'answers' => [
					'ext-quicksurveys-test-internal-survey-positive',
				],
				'audience' => [
					'minEdits' => 'foobar',
				],
			]
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

		$factory = new SurveyFactory( $this->createMock( LoggerInterface::class ) );
		$actual = $factory->newSurvey(
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
			]
		);

		$this->assertEquals( $actual, $expected );
	}

	public function testItShouldThrowWhenThereAreNoAnswers() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" internal survey doesn\'t have any answers.' )
		);
		$factory->newSurvey(
			[
				'name' => 'test',
				'type' => 'internal',
				'layout' => 'single-answer',
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
			]
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
			null,
			'single-answer'
		);

		$factory = new SurveyFactory( $this->createMock( LoggerInterface::class ) );
		$actual = $factory->newSurvey(
			[
				'name' => 'test',
				'type' => 'internal',
				'layout' => 'single-answer',
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
			]
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
			null,
			'single-answer'
		);

		$factory = new SurveyFactory( $this->createMock( LoggerInterface::class ) );
		$actual = $factory->newSurvey(
			[
				'name' => 'test',
				'type' => 'internal',
				'layout' => 'single-answer',
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
			]
		);

		$this->assertEquals( $actual, $expected );
	}

	public function testItShouldThrowIfTheTypeIsNotRecognized() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" survey isn\'t marked as internal or ' .
				'external.' )
		);
		$factory->newSurvey(
			[
				'name' => 'test',
				'type' => 'ixternal',
				'question' => 'Do you like writing unit tests?',
				'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
			]
		);
	}

	public function testItShouldMarkTheSurveyAsDisabledByDefault() {
		$factory = new SurveyFactory( $this->createMock( LoggerInterface::class ) );
		$survey = $factory->newSurvey(
			[
				'name' => 'test',
				'type' => 'internal',
				'layout' => 'single-answer',
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
			]
		);

		$this->assertFalse( $survey->isEnabled() );
	}

	public function testItShouldThrowWhenThereIsNoCoverage() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" survey doesn\'t have a coverage.' )
		);
		$factory->newSurvey(
			[
				'name' => 'test',
				'type' => 'internal',
				'layout' => 'single-answer',
				'question' => 'Do you like writing unit tests?',
				'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
			]
		);
	}

	public function testItShouldUseDefaultLayout() {
		$factory = new SurveyFactory( $this->createMock( LoggerInterface::class ) );
		$survey = $factory->newSurvey(
			[
				'name' => 'test',
				'type' => 'internal',
				'coverage' => 0.5,
				'platforms' => [
					'desktop' => [
						'stable',
					],
				],
				'question' => 'Do you like writing unit tests?',
				'answers' => [
					'ext-quicksurveys-test-internal-survey-positive',
				],
				'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
			]
		);

		$this->assertSame( 'single-answer', $survey->toArray()['layout'] );
	}

	public function testItShouldThrowWhenThereIsBadLayout() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" internal survey layout is not one of ' .
				'"single-answer" or "multiple-answer".' )
		);
		$factory->newSurvey(
			[
				'name' => 'test',
				'type' => 'internal',
				'layout' => 'garbage',
				'coverage' => 0.5,
				'platforms' => [
					'desktop' => [
						'stable',
					],
				],
				'question' => 'Do you like writing unit tests?',
				'answers' => [
					'ext-quicksurveys-test-internal-survey-positive',
				],
				'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
			]
		);
	}

	/**
	 * @dataProvider provideInvalidPlatforms
	 */
	public function testItShouldThrowWhenPlatformsIsInvalid( $platforms, $expectedMessage ) {
		$factory = new SurveyFactory(
			$this->expectsErrorLog( 'Bad survey configuration: ' . $expectedMessage )
		);
		$survey = $factory->newSurvey(
			[
				'name' => 'test',
				'type' => 'internal',
				'layout' => 'single-answer',
				'question' => 'Do you like writing unit tests?',
				'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
				'coverage' => 1,
				'platforms' => $platforms,
			]
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
