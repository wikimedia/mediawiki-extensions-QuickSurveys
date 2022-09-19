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
class SurveyFactoryTest extends \MediaWikiIntegrationTestCase {

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
				'link' => 'ext-quicksurveys-example-external-survey-link',
			]
		);
	}

	public function testItShouldThrowWhenThereIsNoHttpsPresent() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" external survey must have a secure url.' )
		);
		$factory->newSurvey(
			[
				'name' => 'test',
				'type' => 'external',
				'question' => 'Do you feel safe?',
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
				'link' => 'ext-quicksurveys-test-external-survey-no-http-link"',
				'privacyPolicy' => 'ext-quicksurveys-test-external-survey-privacy-policy',
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
				null,
				null,
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
			null,
			null,
			new SurveyAudience( [
				'minEdits' => 100,
				'userAgent' => [ 'KaiOS' ]
			] ),
			'ext-quicksurveys-example-external-survey-link',
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
				null,
				null,
				'audience' => [
					'minEdits' => 100,
					'userAgent' => [ 'KaiOS' ]
				],
				'link' => 'ext-quicksurveys-example-external-survey-link',
				'privacyPolicy' => 'ext-quicksurveys-test-external-survey-privacy-policy',
				null,
				null,
				new SurveyAudience( [] )
			]
		);

		$this->assertEquals( $expected, $actual );
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
			null,
			null,
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

		$this->assertEquals( $expected, $actual );
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
			null,
			null,
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

		$this->assertEquals( $expected, $actual );
	}

	public function testItShouldThrowIfThereIsNoName() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog( 'Bad survey configuration: The survey name does not have a value' )
		);
		$this->assertSame( [], $factory->parseSurveyConfig( [ [] ] ) );
	}

	public function testItShouldThrowIfTheSurveyNameIsNotUnique() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog( 'Bad survey configuration: The survey name "test" is not unique' )
		);

		$specs = [
			[
				'name' => 'test',
				'type' => 'external',
				'question' => 'Do you like writing unit tests?',
				'enabled' => true,
			],
			[
				'name' => ' test ',
				'type' => 'external',
				'question' => 'Do you like writing unit tests?',
				'enabled' => true,
			],
		];

		$this->assertSame( [], $factory->parseSurveyConfig( $specs ) );
	}

	public function testParseSurveyConfigSucceeds() {
		$factory = new SurveyFactory( $this->createMock( LoggerInterface::class ) );
		$specs = [
			[
				'name' => 'a',
				'type' => 'internal',
				'question' => '',
				'enabled' => true,
				'coverage' => 1,
				'platforms' => [],
				'answers' => [],
			],
			[
				'name' => 'aa',
				'type' => 'internal',
				'question' => '',
				'enabled' => true,
				'coverage' => 1,
				'platforms' => [],
				'answers' => [],
			],
		];
		$this->assertCount( 2, $factory->parseSurveyConfig( $specs ) );
	}

	public function testItShouldThrowIfTheTypeIsNotRecognized() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" survey isn\'t marked as internal or ' .
				'external.' )
		);
		$survey = $factory->newSurvey(
			[
				'name' => 'test',
				'type' => 'ixternal',
				'question' => 'Do you like writing unit tests?',
			]
		);
		$this->assertNull( $survey );
	}

	public function testItShouldMarkTheSurveyAsDisabledByDefault() {
		$factory = new SurveyFactory( $this->createMock( LoggerInterface::class ) );
		$survey = $factory->newSurvey(
			[
				'name' => 'test',
				'type' => 'internal',
				'layout' => 'single-answer',
				'question' => 'Do you like writing unit tests?',
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
		$survey = $factory->newSurvey(
			[
				'name' => 'test',
				'type' => 'internal',
				'layout' => 'single-answer',
				'question' => 'Do you like writing unit tests?',
			]
		);
		$this->assertNull( $survey );
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
		$survey = $factory->newSurvey(
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
			]
		);
		$this->assertNull( $survey );
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

	private function expectsErrorLog( string $message ): LoggerInterface {
		$logger = $this->createMock( LoggerInterface::class );
		$logger
			->expects( $this->atLeastOnce() )
			->method( 'error' )
			->with( $message );
		return $logger;
	}
}
