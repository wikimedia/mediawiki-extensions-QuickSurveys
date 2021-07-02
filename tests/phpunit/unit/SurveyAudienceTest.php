<?php

namespace Tests\QuickSurveys;

use QuickSurveys\SurveyAudience;
use Wikimedia\Assert\ParameterTypeException;

/**
 * @covers \QuickSurveys\SurveyFactory
 */
class SurveyAudienceTest extends \MediaWikiUnitTestCase {
	/**
	 * @dataProvider provideValidAudience
	 */
	public function testItShouldSerializeCorrectly( $definition, $expected ) {
		$survey = new SurveyAudience( $definition );
		$this->assertEquals( $survey->toArray(), $expected,
			'audience serializes in unexpected way' );
	}

	/**
	 * @dataProvider provideInvalidAudience
	 */
	public function testItShouldThrowWhenAudienceBadlyDefined( $definition, $why ) {
		try {
			new SurveyAudience( $definition );
			$this->assertTrue( false, $why );
		} catch ( ParameterTypeException $e ) {
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
					'anons' => false,
					'minEdits' => 5,
					'maxEdits' => 10,
					'registrationEnd' => '2018-01-01',
					'registrationStart' => '2019-12-31',
					'userAgent' => [ 'KaiOS', 'Chrome' ]
				],
				[
					'anons' => false,
					'minEdits' => 5,
					'maxEdits' => 10,
					'registrationEnd' => '2018-01-01',
					'registrationStart' => '2019-12-31',
					'userAgent' => [ 'KaiOS', 'Chrome' ]
				],
				'a perfectly valid survey'
			],
			[
				[
					'pageIds' => [],
				],
				[
					'pageIds' => [],
				],
				'empty page filter is allowed, but will never match'
			],
			[
				[
					'pageIds' => [ 123, 456 ],
				],
				[
					'pageIds' => [ 123, 456 ],
				],
				'filter to match a couple of pages'
			],
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
			[
				[
					'anons' => 1
				],
				'anons must be a boolean'
			],
			[
				[
					'registrationStart' => 1553877136,
				],
				'registrationStart must be date string in format YYYY-MM-DD'
			],
			[
				[
					'registrationEnd' => 1553877136,
				],
				'registrationEnd must be date string in format YYYY-MM-DD'
			],
		];
	}
}
