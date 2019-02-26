<?php

namespace Tests\QuickSurveys;

use QuickSurveys\SurveyAudience;

/**
 * @covers \QuickSurveys\SurveyFactory
 */
class SurveyAudienceTest extends \PHPUnit\Framework\TestCase {
	/**
	 * @dataProvider provideValidAudience
	 */
	public function testItShouldSerializeCorrectly( $definition, $expected ) {
		$survey = new SurveyAudience( $definition );
		$this->assertSame( $survey->toArray(), $expected,
			'audience serializes in unexpected way' );
	}

	/**
	 * @dataProvider provideInvalidAudience
	 * @expectedException InvalidArgumentException
	 */
	public function testItShouldThrowWhenAudienceBadlyDefined( $definition, $why ) {
		try {
			$survey = new SurveyAudience( $definition );
		} catch ( InvalidArgumentException $e ) {
			$this->assertTrue( true, $why );
		}
	}

	public function provideValidAudience() {
		return [
			[
				[],
				[],
				'surveys can be run without audience'
			],
			[
				[
					'is' => 'giraffe',
				],
				[],
				'`is` is not a valid audience configuration option and dropped silently'
			],
			[
				[
					'minEdits' => 5,
					'maxEdits' => 10,
				],
				[
					'minEdits' => 5,
					'maxEdits' => 10,
				],
				'a perfectly valid survey'
			]
		];
	}
	public function provideInvalidAudience() {
		return [
			[
				[
					'minEdits' => 'banana',
					'maxEdits' => 10,
				],
				'minEdits must be a number'
			],
			[
				[
					'minEdits' => 5,
					'maxEdits' => 'banana',
				],
				'maxEdits must be a number'
			],
			[
				[
					'minEdits' => 'banana',
					'maxEdits' => 'banana',
				],
				'minEdits and maxEdits must be a number'
			],
		];
	}
}
