<?php

namespace Tests\QuickSurveys;

use QuickSurveys\SurveyQuestion;
use Wikimedia\Assert\ParameterTypeException;

/**
 * @covers \QuickSurveys\SurveyQuestion
 */
class SurveyQuestionTest extends \MediaWikiUnitTestCase {
	/**
	 * @dataProvider provideValidQuestion
	 */
	public function testItShouldSerializeCorrectly(
		string $type, array $definition, array $expected
	) {
		$survey = new SurveyQuestion( $definition, $type );
		$this->assertEquals( $expected, $survey->toArray(),
			'the question serialized in an unexpected way' );
	}

	/**
	 * @dataProvider provideInvalidQuestion
	 */
	public function testItShouldThrowWhenAudienceBadlyDefined(
		string $type, array $definition, string $why
	) {
		try {
			new SurveyQuestion( $definition, $type );
			$this->fail( $why );
		} catch ( ParameterTypeException $e ) {
			$this->addToAssertionCount( 1 );
		}
	}

	public function testBasicFunctionality() {
		$internalQuestion = new SurveyQuestion( [
			'name' => 'question-1',
			'layout' => 'layout',
			'question' => 'question',
			'description' => 'description',
			'shuffleAnswersDisplay' => false,
			'answers' => [
				[
					'label' => 'answer1',
					'freeformTextLabel' => 'freeformTextLabel1',
				],
				[ 'label' => 'answer2' ],
			],
		], 'internal' );
		$externalQuestion = new SurveyQuestion( [
			'name' => 'question-1',
			'question' => 'question',
			'description' => 'description',
			'link' => 'link',
			'instanceTokenParameterName' => 'instanceTokenParameterName',
			'yesMsg' => 'yes',
			'noMsg' => 'no',
		], 'external' );

		$this->assertSame( [
			'question',
			'description',
			'answer1',
			'freeformTextLabel1',
			'answer2',
		], $internalQuestion->getMessages() );
		$this->assertSame( [
			'question',
			'description',
			'link',
			'yes',
			'no',
		], $externalQuestion->getMessages() );
	}

	public static function provideValidQuestion() {
		return [
			[
				'external',
				[
					'name' => 'question-1',
					'question' => 'Do you like writing unit tests?',
					'instanceTokenParameterName' => '',
					'yesMsg' => 'Visit survey',
					'noMsg' => 'No thanks',
				],
				[
					'name' => 'question-1',
					'question' => 'Do you like writing unit tests?',
					'instanceTokenParameterName' => '',
					'yesMsg' => 'Visit survey',
					'noMsg' => 'No thanks',
				],
				'All of these keys are valid and should pass through without issue',
			],
			[
				'internal',
				[
					'name' => 'question-1',
					'layout' => 'single-answer',
					'question' => 'Do you like writing unit tests?',
					'answers' => [
						[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
					],
				],
				[
					'name' => 'question-1',
					'layout' => 'single-answer',
					'question' => 'Do you like writing unit tests?',
					'answers' => [
						[ 'label' => 'ext-quicksurveys-test-internal-survey-positive' ],
					],
				],
				'All of these keys are valid and should pass through without issue',
			],
			[
				'internal',
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
				'All of these keys are valid and should pass through without issue',
			],
			[
				'internal',
				[
					'dependsOn' => [
						[
							'question' => 'question-2',
							'answerIsOneOf' => [ 'ext-quicksurveys-test-internal-survey-positive' ],
							'badKeyThatShouldNotExist' => 12345,
						]
					],
					'name' => 'question-2',
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
				'The key that is not part of the question schema should be ignored',
			],
		];
	}

	public static function provideInvalidQuestion() {
		return [
			[
				'external',
				[
					'name' => 'question-1',
					'question' => 'Do you like writing unit tests?',
					'instanceTokenParameterName' => '',
					'yesMsg' => 0,
					'noMsg' => 'No thanks',
				],
				'yesMsg must be a string',
			],
			[
				'external',
				[
					'name' => 'question-1',
					'question' => 'Do you like writing unit tests?',
					'instanceTokenParameterName' => '',
					'yesMsg' => 'Visit survey',
					'noMsg' => 0,
				],
				'noMsg must be a string',
			],
			[
				'external',
				[
					'name' => 'question-1',
					'question' => 'Do you like writing unit tests?',
					'instanceTokenParameterName' => 0,
					'yesMsg' => 'Visit survey',
					'noMsg' => 'No thanks',
				],
				'instanceTokenParameterName must be a string',
			],
			[
				'external',
				[
					'name' => 'question-1',
					'question' => true,
					'instanceTokenParameterName' => '',
					'yesMsg' => 'Visit survey',
					'noMsg' => 'No thanks',
				],
				'question must be a string',
			],
			[
				'external',
				[
					'name' => true,
					'question' => 'Do you like writing unit tests?',
					'instanceTokenParameterName' => '',
					'yesMsg' => 'Visit survey',
					'noMsg' => 'No thanks',
				],
				'name must be a string',
			],
		];
	}
}
