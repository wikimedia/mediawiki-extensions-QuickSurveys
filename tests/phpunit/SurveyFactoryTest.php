<?php

namespace Tests\QuickSurveys;

use Psr\Log\LoggerInterface;
use QuickSurveys\ExternalSurvey;
use QuickSurveys\InternalSurvey;
use QuickSurveys\SurveyAudience;
use QuickSurveys\SurveyFactory;
use QuickSurveys\SurveyQuestion;

/**
 * @covers \QuickSurveys\SurveyFactory
 */
class SurveyFactoryTest extends \MediaWikiIntegrationTestCase {
	private const INTERNAL_SURVEY = [
		'name' => 'test',
		'type' => 'internal',
		'privacyPolicy' => 'ext-quicksurveys-test-internal-survey-privacy-policy',
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
	];

	private const EXTERNAL_SURVEY = [
		'name' => 'test',
		'type' => 'external',
		'privacyPolicy' => 'ext-quicksurveys-test-external-survey-privacy-policy',
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
	];

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
				'platforms' => [],
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
				'platforms' => [],
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
				'platforms' => [],
				'link' => 'ext-quicksurveys-test-external-survey-no-http-link"',
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
				'layout' => 'multiple-answer',
				'question' => 'Do you like writing unit tests?',
				'enabled' => true,
				'coverage' => 1,
				'platforms' => [],
				'answers' => [
					'ext-quicksurveys-test-internal-survey-positive',
					'ext-quicksurveys-test-internal-survey-negative',
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
			[
				new SurveyQuestion( [
					'name' => 'question-1',
					'question' => 'Do you like writing unit tests?',
					'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
					'link' => 'ext-quicksurveys-example-external-survey-link',
					'instanceTokenParameterName' => '',
				], 'external' ),
			],
			null,
			null,
			null,
			null,
			null,
			null,
			null
		);

		$factory = new SurveyFactory( $this->createMock( LoggerInterface::class ) );
		$actual = $factory->newSurvey(
			[
				'name' => 'test',
				'type' => 'external',
				'questions' => [
					[
						'name' => 'question-1',
						'question' => 'Do you like writing unit tests?',
						'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
						'link' => 'ext-quicksurveys-example-external-survey-link',
						'instanceTokenParameterName' => '',
					],
				],
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
					'userAgent' => [ 'KaiOS' ]
				],
				'privacyPolicy' => 'ext-quicksurveys-test-external-survey-privacy-policy',
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
				'platforms' => [],
			]
		);
	}

	public function testItShouldFactoryAnInternalSurvey() {
		$expected = new InternalSurvey(
			'test',
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
			null,
			null,
			null,
			new SurveyAudience( [] ),
			[
				new SurveyQuestion( [
					'name' => 'question-1',
					'layout' => 'single-answer',
					'question' => 'Do you like writing unit tests?',
					'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
					'shuffleAnswersDisplay' => true,
					'answers' => [
						[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
					],
				], 'internal' ),
			],
			null,
			null,
			null,
			null,
			null,
			null,
			null,
			null
		);

		$factory = new SurveyFactory( $this->createMock( LoggerInterface::class ) );
		$actual = $factory->newSurvey(
			[
				'name' => 'test',
				'type' => 'internal',
				'questions' => [
					[
						'name' => 'question-1',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
						'shuffleAnswersDisplay' => true,
						'answers' => [
							[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
						],
					],
				],
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
			]
		);

		$this->assertEquals( $expected, $actual );
	}

	public function testItShouldFactoryAnInternalSurveyWithShuffleAnswersDisplayDisabled() {
		$expected = new InternalSurvey(
			'test',
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
			null,
			null,
			null,
			new SurveyAudience( [] ),
			[
				new SurveyQuestion( [
					'name' => 'question-1',
					'layout' => 'single-answer',
					'question' => 'Do you like writing unit tests?',
					'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
					'shuffleAnswersDisplay' => false,
					'answers' => [
						[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
					],
				], 'internal' ),
			],
			null,
			null,
			null,
			null,
			null,
			null,
			null,
			null
		);

		$factory = new SurveyFactory( $this->createMock( LoggerInterface::class ) );
		$actual = $factory->newSurvey(
			[
				'name' => 'test',
				'type' => 'internal',
				'questions' => [
					[
						'name' => 'question-1',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
						'answers' => [
							[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
						],
						'shuffleAnswersDisplay' => false,
					],
				],
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

		$spec = [
			'type' => 'external',
			'question' => 'Do you like writing unit tests?',
			'enabled' => true,
		];
		$specs = [
			[ 'name' => 'test' ] + $spec,
			[ 'name' => ' test ' ] + $spec,
		];

		$this->assertSame( [], $factory->parseSurveyConfig( $specs ) );
	}

	public function testParseSurveyConfigSucceeds() {
		$factory = new SurveyFactory( $this->createMock( LoggerInterface::class ) );
		$spec = [
			'type' => 'internal',
			'questions' => [
				[
					'name' => 'question-1',
					'layout' => 'single-answer',
					'question' => 'Do you like writing unit tests?',
					'answers' => [
						[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ]
					],
				],
			],
			'enabled' => true,
			'coverage' => 1,
			'platforms' => [],
		];
		$specs = [
			[ 'name' => 'a' ] + $spec,
			[ 'name' => 'aa' ] + $spec,
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
				'platforms' => [],
				'question' => 'Do you like writing unit tests?',
				'answers' => [ 'ext-quicksurveys-test-internal-survey-positive' ],
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

	public static function provideInvalidPlatforms() {
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

	public function testItShouldLogWhenSurveyConfigurationIsNotAList() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad surveys configuration: The surveys configuration is not a list.'
			)
		);

		$this->assertSame(
			[],
			$factory->parseSurveyConfig( [
				'name' => 'test',
			] )
		);
	}

	private function expectsErrorLog( string $message ): LoggerInterface {
		$logger = $this->createMock( LoggerInterface::class );
		$logger
			->expects( $this->atLeastOnce() )
			->method( 'error' )
			->with( $message );
		return $logger;
	}

	public function testItShouldFactoryAnExternalSurveyWithAnswers(): void {
		$factory = new SurveyFactory( $this->createMock( LoggerInterface::class ) );
		$survey = $factory->newSurvey(
			[
				'name' => 'test',
				'type' => 'external',
				'questions' => [
					[
						'name' => 'question-1',
						'question' => 'Do you like writing unit tests?',
						'description' => 'A survey for (potential) developers of the QuickSurveys extension.',
						'link' => 'ext-quicksurveys-example-external-survey-link',
						'yesMsg' => 'Visit survey',
						'noMsg' => 'No thanks',
					],
				],
				'enabled' => false,
				'coverage' => 1,
				'platforms' => [],
				'privacyPolicy' => 'ext-quicksurveys-test-external-survey-privacy-policy',
			]
		);
		$array = $survey->toArray();

		$this->assertSame( 'Visit survey', $array['questions'][0]['yesMsg'] );
		$this->assertSame( 'No thanks', $array['questions'][0]['noMsg'] );
	}

	public function testItShouldThrowWhenThereAreNoQuestions(): void {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" survey doesn\'t have a question.' )
		);
		$survey = $factory->newSurvey(
			[
				'name' => 'test',
				'questions' => [],
			]
		);
		$this->assertNull( $survey );
	}

	public function testItShouldThrowWhenAQuestionHasNoName() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: Question at index "0" in the "test" internal survey ' .
				'doesn\'t have a name.' )
		);
		$survey = $factory->newSurvey(
			array_merge( self::INTERNAL_SURVEY, [
				'questions' => [
					[
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'answers' => [
							[
								'label' => 'ext-quicksurveys-test-internal-survey-positive',
							],
						],
					],
				],
			] )
		);
		$this->assertNull( $survey );
	}

	public function testItShouldUseDefaultLayoutForMultipleQuestions() {
		$factory = new SurveyFactory( $this->createMock( LoggerInterface::class ) );
		$survey = $factory->newSurvey(
			array_merge( self::INTERNAL_SURVEY, [
				'questions' => [
					[
						'name' => 'test',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'answers' => [
							[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
						],
					]
				],
			] )
		);

		$this->assertSame( 'single-answer', $survey->toArray()['questions'][0]['layout'] );
	}

	public function testItShouldThrowWhenAQuestionHasABadLayout() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: Question at index "0" in the "test" internal survey ' .
				'has a layout that\'s not one of "single-answer" or "multiple-answer".' )
		);
		$survey = $factory->newSurvey(
			array_merge( self::INTERNAL_SURVEY, [
				'questions' => [
					[
						'name' => 'test',
						'layout' => 'invalid-layout',
						'question' => 'Do you like writing unit tests?',
						'answers' => [
							[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
						],
					]
				],
			] )
		);
		$this->assertNull( $survey );
	}

	public function testItShouldThrowWhenAQuestionHasNoAnswers() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: Question at index "0" in the "test" internal survey ' .
				'has no answers.' )
		);
		$survey = $factory->newSurvey(
			array_merge( self::INTERNAL_SURVEY, [
				'questions' => [
					[
						'name' => 'test',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
					]
				],
			] )
		);
		$this->assertNull( $survey );
	}

	public function testItShouldThrowWhenAQuestionHasEmptyAnswers() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: Question at index "0" in the "test" internal survey ' .
				'has no answers.' )
		);
		$survey = $factory->newSurvey(
			array_merge( self::INTERNAL_SURVEY, [
				'questions' => [
					[
						'name' => 'test',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'answers' => [],
					]
				],
			] )
		);
		$this->assertNull( $survey );
	}

	public function testItShouldAllowAnswersWithALabelString() {
		$factory = new SurveyFactory( $this->createMock( LoggerInterface::class ) );
		$survey = $factory->newSurvey(
			array_merge( self::INTERNAL_SURVEY, [
				'questions' => [
					[
						'name' => 'test',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'answers' => [
							[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
						],
					]
				],
			] )
		);
		$this->assertNotNull( $survey );
	}

	public function testItShouldAllowAnswersWithAFreeFormTextLabel() {
		$factory = new SurveyFactory( $this->createMock( LoggerInterface::class ) );
		$survey = $factory->newSurvey(
			array_merge( self::INTERNAL_SURVEY, [
				'questions' => [
					[
						'name' => 'test',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'answers' => [
							[
								'label' => 'ext-quicksurveys-test-internal-survey-positive',
								'freeformTextLabel' => 'ext-quicksurveys-test-free-text-placeholder',
							],
						],
					]
				],
			] )
		);
		$this->assertNotNull( $survey );
	}

	public function testItShouldThrowWhenQuestionIsMissingLabel() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: Question at index "0" in the "test" internal survey ' .
				'has an answer with no label.' )
		);
		$survey = $factory->newSurvey(
			array_merge( self::INTERNAL_SURVEY, [
				'questions' => [
					[
						'name' => 'test',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'answers' => [
							[ 'freeformTextLabel' => 'ext-quicksurveys-test-free-text-placeholder' ],
						],
					]
				],
			] )
		);
		$this->assertNull( $survey );
	}

	public function testItShouldThrowWhenAQuestionHasABadShuffleAnswersDisplay() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: Bad value for parameter shuffleAnswersDisplay: must be a boolean' )
		);
		$survey = $factory->newSurvey(
			array_merge( self::INTERNAL_SURVEY, [
				'questions' => [
					[
						'name' => 'test',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'answers' => [
							[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
						],
						'shuffleAnswersDisplay' => 'tralse',
					]
				],
			] )
		);
		$this->assertNull( $survey );
	}

	public function testItShouldThrowIfDependsOnAQuestionThatDoesNotExist() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: Question at index "1" in the "test" internal survey ' .
				'depends on a question that does not exist prior to itself.' )
		);
		$survey = $factory->newSurvey(
			array_merge( self::INTERNAL_SURVEY, [
				'questions' => [
					[
						'name' => 'question-1',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'answers' => [
							[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
						],
					],
					[
						'dependsOn' => [
							[ 'question' => 'non-existent-question' ],
						],
						'name' => 'question-2',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'answers' => [
							[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
						],
					],
				],
			] )
		);
		$this->assertNull( $survey );
	}

	public function testItShouldAllowIfDependsOnQuestionThatExists() {
		$factory = new SurveyFactory( $this->createMock( LoggerInterface::class ) );
		$survey = $factory->newSurvey(
			array_merge( self::INTERNAL_SURVEY, [
				'questions' => [
					[
						'name' => 'question-1',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'answers' => [
							[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
						],
					],
					[
						'dependsOn' => [
							[ 'question' => 'question-1' ]
						],
						'name' => 'question-2',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'answers' => [
							[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
						],
					],
				],
			] )
		);
		$this->assertNotNull( $survey );
	}

	public function testItShouldAllowAQuestionWithoutDependsOn() {
		$factory = new SurveyFactory( $this->createMock( LoggerInterface::class ) );
		$survey = $factory->newSurvey(
			array_merge( self::INTERNAL_SURVEY, [
				'questions' => [
					[
						'name' => 'test',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'answers' => [
							[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
						],
					]
				],
			] )
		);
		$this->assertNotNull( $survey );
	}

	public function testItShouldAllowAQuestionWhenDependsOnIsEmpty() {
		$factory = new SurveyFactory( $this->createMock( LoggerInterface::class ) );
		$survey = $factory->newSurvey(
			array_merge( self::INTERNAL_SURVEY, [
				'questions' => [
					[
						'dependsOn' => [],
						'name' => 'test',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'answers' => [
							[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
						],
					]
				],
			] )
		);
		$this->assertNotNull( $survey );
	}

	public function testItShouldThrowIfAnswerIsOneOfAnswerIsNotOnTheQuestion() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: Question at index "1" in the "test" internal survey ' .
				'depends on an answer that doesn\'t exist on the referenced question.' )
		);
		$survey = $factory->newSurvey(
			array_merge( self::INTERNAL_SURVEY, [
				'questions' => [
					[
						'name' => 'question-1',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'answers' => [
							[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
						],
					],
					[
						'dependsOn' => [
							[
								'question' => 'question-1',
								'answerIsOneOf' => [ 'ext-quicksurveys-test-internal-survey-negative' ],
							]
						],
						'name' => 'question-2',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'answers' => [
							[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
						],
					],
				],
			] )
		);
		$this->assertNull( $survey );
	}

	public function testItShouldAllowIfDependsOnAnswerThatExists() {
		$factory = new SurveyFactory( $this->createMock( LoggerInterface::class ) );
		$survey = $factory->newSurvey(
			array_merge( self::INTERNAL_SURVEY, [
				'questions' => [
					[
						'name' => 'question-1',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'answers' => [
							[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
						],
					],
					[
						'dependsOn' => [
							[
								'question' => 'question-1',
								'answerIsOneOf' => [ 'ext-quicksurveys-test-internal-survey-positive' ],
							]
						],
						'name' => 'question-2',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'answers' => [
							[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
						],
					],
				],
			] )
		);
		$this->assertNotNull( $survey );
	}

	public function testItShouldThrowIfThereIsQuestionWithDuplicateName() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: Question at index "1" in the "test" internal survey ' .
				'has a name that\'s used by a previous question.' )
		);
		$survey = $factory->newSurvey(
			array_merge( self::INTERNAL_SURVEY, [
				'questions' => [
					[
						'name' => 'question-1',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'answers' => [
							[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
						],
					],
					[
						'dependsOn' => [
							[
								'question' => 'question-1',
								'answerIsOneOf' => [ 'ext-quicksurveys-test-internal-survey-positive' ],
							]
						],
						'name' => 'question-1',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'answers' => [
							[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
						],
					],
				],
			] )
		);
		$this->assertNull( $survey );
	}

	public function testItShouldThrowIfQuestionIsEmpty() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: Question at index "0" in the "test" internal survey ' .
				'doesn\'t have a question.' )
		);
		$survey = $factory->newSurvey(
			array_merge( self::INTERNAL_SURVEY, [
				'questions' => [
					[
						'name' => 'question-1',
						'layout' => 'single-answer',
						'question' => '',
						'answers' => [
							[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
						],
					],
				],
			] )
		);
		$this->assertNull( $survey );
	}

	public function testItShouldThrowIfQuestionHasDependencyWithNoName() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: Question at index "1" in the "test" internal survey ' .
				'has a dependency that is not referencing any question.' )
		);
		$survey = $factory->newSurvey(
			array_merge( self::INTERNAL_SURVEY, [
				'questions' => [
					[
						'name' => 'question-1',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'answers' => [
							[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
						],
					],
					[
						'dependsOn' => [
							[ 'answerIsOneOf' => [ 'ext-quicksurveys-test-internal-survey-positive' ] ],
						],
						'name' => 'question-2',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'answers' => [
							[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
						],
					],
				],
			] )
		);
		$this->assertNull( $survey );
	}

	public function testItShouldThrowIfQuestionReferencesItselfAsDependency() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: Question at index "1" in the "test" internal survey ' .
				'is referencing itself as a question it depends on.' )
		);
		$survey = $factory->newSurvey(
			array_merge( self::INTERNAL_SURVEY, [
				'questions' => [
					[
						'name' => 'question-1',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'answers' => [
							[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
						],
					],
					[
						'dependsOn' => [
							[
								'question' => 'question-2',
								'answerIsOneOf' => [ 'ext-quicksurveys-test-internal-survey-positive' ],
							]
						],
						'name' => 'question-2',
						'layout' => 'single-answer',
						'question' => 'Do you like writing unit tests?',
						'answers' => [
							[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
						],
					],
				],
			] )
		);
		$this->assertNull( $survey );
	}

	public function testItShouldFactoryExternalSurveyWithMultiQuestionSchema() {
		$factory = new SurveyFactory( $this->createMock( LoggerInterface::class ) );
		$survey = $factory->newSurvey(
			array_merge( self::EXTERNAL_SURVEY, [
				'questions' => [
					[
						'name' => 'question-1',
						'question' => 'Do you like writing unit tests?',
						'link' => 'ext-quicksurveys-example-external-survey-link',
						'instanceTokenParameterName' => '',
						'yesMsg' => 'Visit survey',
						'noMsg' => 'No thanks',
					],
				],
			] )
		);
		$this->assertNotNull( $survey );
	}

	public function testItShouldThrowIfExternalSurveyLinkIsMissing() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" external survey doesn\'t have a link.' )
		);
		$survey = $factory->newSurvey(
			array_merge( self::EXTERNAL_SURVEY, [
				'questions' => [
					[
						'name' => 'question-1',
						'question' => 'Do you like writing unit tests?',
						'instanceTokenParameterName' => '',
						'yesMsg' => 'Visit survey',
						'noMsg' => 'No thanks'
					],
				],
			] )
		);
		$this->assertNull( $survey );
	}

	public function testItShouldThrowIfExternalSurveyQuestionIsMissing() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" external survey doesn\'t have a question.' )
		);
		$survey = $factory->newSurvey(
			array_merge( self::EXTERNAL_SURVEY, [
				'questions' => [
					[
						'name' => 'question-1',
						'question' => '',
						'link' => 'ext-quicksurveys-example-external-survey-link',
						'instanceTokenParameterName' => '',
						'yesMsg' => 'Visit survey',
						'noMsg' => 'No thanks'
					],
				],
			] )
		);
		$this->assertNull( $survey );
	}

	public function testItShouldThrowIfExternalSurveyQuestionNameIsMissing() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" external survey doesn\'t have a question name.' )
		);
		$survey = $factory->newSurvey(
			array_merge( self::EXTERNAL_SURVEY, [
				'questions' => [
					[
						'question' => 'Do you like writing unit tests?',
						'link' => 'ext-quicksurveys-example-external-survey-link',
						'instanceTokenParameterName' => '',
						'yesMsg' => 'Visit survey',
						'noMsg' => 'No thanks'
					],
				],
			] )
		);
		$this->assertNull( $survey );
	}

	public function testItShouldThrowIfExternalSurveyHasMoreThanOneQuestion() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" external survey should only have one question.' )
		);
		$survey = $factory->newSurvey(
			array_merge( self::EXTERNAL_SURVEY, [
				'questions' => [
					[
						'name' => 'question-1',
						'question' => 'Do you like writing unit tests?',
						'link' => 'ext-quicksurveys-example-external-survey-link',
						'instanceTokenParameterName' => '',
						'yesMsg' => 'Visit survey',
						'noMsg' => 'No thanks'
					],
					[
						'name' => 'question-2',
						'question' => 'Do you like writing unit tests?',
						'link' => 'ext-quicksurveys-example-external-survey-link',
						'instanceTokenParameterName' => '',
						'yesMsg' => 'Visit survey',
						'noMsg' => 'No thanks'
					],
				],
			] )
		);
		$this->assertNull( $survey );
	}

	public function testItShouldThrowIfExternalSurveyHasNoQuestions() {
		$factory = new SurveyFactory(
			$this->expectsErrorLog(
				'Bad survey configuration: The "test" survey doesn\'t have a question.' )
		);
		$survey = $factory->newSurvey(
			array_merge( self::EXTERNAL_SURVEY, [ 'questions' => [] ] )
		);
		$this->assertNull( $survey );
	}

	public function testItShouldNotRequireTopLevelLayoutIfMultipleQuestions() {
		$factory = new SurveyFactory( $this->createMock( LoggerInterface::class ) );
		$survey = $factory->newSurvey(
			array_merge( self::INTERNAL_SURVEY, [
				'name' => 'internal multi question and answer example survey',
				'type' => 'internal',
				'questions' => [
					[
						'name' => 'question-1',
						'layout' => 'multiple-answer',
						'question' => 'This is the first question.',
						'answers' => [
							[ 'label' => 'ext-quicksurveys-example-internal-survey-answer-positive' ],
							[ 'label' => 'ext-quicksurveys-example-internal-survey-answer-neutral' ],
							[ 'label' => 'ext-quicksurveys-example-internal-survey-answer-negative' ],
						],
						'shuffleAnswersDisplay' => true,
					],
					[
						'name' => 'question-2',
						'layout' => 'multiple-answer',
						'question' => 'This is the second question.',
						'answers' => [
							[ 'label' => 'ext-quicksurveys-example-internal-survey-answer-positive' ],
							[ 'label' => 'ext-quicksurveys-example-internal-survey-answer-neutral' ],
							[ 'label' => 'ext-quicksurveys-example-internal-survey-answer-negative' ],
						],
						'shuffleAnswersDisplay' => true,
					],
				],
			] )
		);
		$this->assertNotNull( $survey );
	}
}
