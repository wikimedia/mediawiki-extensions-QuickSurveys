'use strict';

const utils = require( '../../resources/ext.quicksurveys.lib/vue/utils.js' );

describe( 'utils', () => {
	describe( 'extend', () => {
		it( 'should extend the keys of an object', () => {
			const result = {};
			utils.extend( result, { a: 1, b: 2 } );
			expect( result ).toEqual( { a: 1, b: 2 } );
		} );
	} );

	describe( 'getCountryCode', () => {
		it( 'should return Geo.country', () => {
			const originalWindowGeo = window.Geo,
				originalGeo = global.Geo;

			window.Geo = {};
			global.Geo = { country: 'CA' };
			expect( utils.getCountryCode() ).toBe( 'CA' );

			window.Geo = originalWindowGeo;
			global.Geo = originalGeo;
		} );
	} );

	describe( 'shuffleAnswers', () => {
		it( 'should shuffle the array of answers', () => {
			const answers = [ 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l' ];
			const result = utils.shuffleAnswers( Array.from( answers ) );
			expect( result ).not.toEqual( answers );
			expect( result.length ).toBe( answers.length );
			for ( let idx = 0; idx < answers.length; idx++ ) {
				expect( result.indexOf( answers[ idx ] ) ).toBeGreaterThanOrEqual( 0 );
			}
		} );
	} );

	describe( 'processSurveyQuestions', () => {
		it( 'should return empty array', () => {
			const result = utils.processSurveyQuestions();
			expect( result ).toEqual( [] );
		} );

		it( 'should properly parse pageViewToken for external link', () => {
			const questions = [
					{
						name: 'Question',
						link: 'qunit-message-link-special-page',
						instanceTokenParameterName: 'aFakeTokenParam'
					}
				],
				result = utils.processSurveyQuestions( questions, 'token' );

			expect( result[ 0 ].externalLink ).toBe(
				'https://en.wikipedia.org/wiki/Special:QuickSurvey?aFakeTokenParam=token'
			);
		} );

		it( 'should properly format answers array', () => {
			const questions = [
					{
						name: 'Question',
						question: 'A Question',
						answers: [
							{ label: 'An Answer', freeformTextLabel: 'A Placeholder' },
							{ label: 'Another Answer' }
						]
					}
				],
				result = utils.processSurveyQuestions( questions );
			expect( result[ 0 ].answers ).toEqual( [
				{
					key: 'An Answer',
					label: 'An Answer',
					freeformTextLabel: 'A Placeholder'
				},
				{
					key: 'Another Answer',
					label: 'Another Answer'
				}
			] );
		} );

		it( 'should shuffle answers array', () => {
			const questions = [
					{
						name: 'Question',
						question: 'A Question',
						shuffleAnswersDisplay: true,
						answers: [
							{ label: 'Answer 1' },
							{ label: 'Answer 2' },
							{ label: 'Answer 3' },
							{ label: 'Answer 4' },
							{ label: 'Answer 5' },
							{ label: 'Answer 6' },
							{ label: 'Answer 7' },
							{ label: 'Answer 8' },
							{ label: 'Answer 9' },
							{ label: 'Answer 10' }
						]
					}
				],
				result = utils.processSurveyQuestions( questions ),
				answersWithKeys = questions[ 0 ].answers.map( ( answer ) => ( { key: answer.label, label: answer.label } ) );
			expect( result[ 0 ].answers.length ).toBe( 10 );
			for ( let idx = 0; idx < answersWithKeys.length; idx++ ) {
				const answerInResult = result[ 0 ].answers.find( ( answer ) => answer.key === answersWithKeys[ idx ].key );
				expect( answerInResult ).toBeTruthy();
				expect( answerInResult.label ).toBe( answersWithKeys[ idx ].label );
			}
		} );
	} );

	describe( 'getNextQuestionIndex', () => {
		const questions = [
			{ name: 'Q1', questionKey: 'q1' },
			{
				name: 'Q2',
				questionKey: 'q2',
				dependsOn: [ { question: 'Q1', answerIsOneOf: [ 'Yes' ] } ] },
			{
				name: 'Q3',
				questionKey: 'q3',
				dependsOn: [ { question: 'Q2', answerIsOneOf: [ 'Yes', 'Maybe' ] } ]
			},
			{ name: 'Q4', questionKey: 'q4' },
			{
				name: 'Q5',
				questionKey: 'q5',
				dependsOn: [
					{ question: 'Q1', answerIsOneOf: [ 'Yes' ] },
					{ question: 'Q4', answerIsOneOf: [ 'Maybe', 'No' ] }
				]
			},
			{ name: 'Q6', questionKey: 'q6' }
		];

		it( 'should return the next index for a question without dependencies',
			() => {
				const answers = [];
				expect( utils.getNextQuestionIndex( 2, questions, answers ) ).toBe( 3 );
			}
		);

		it( 'should return Infinity if it is the last question', () => {
			const answers = [];
			expect( utils.getNextQuestionIndex( 5, questions, answers ) ).toBe( null );
		} );

		it( 'should skip to the next available question if the dependency is not met',
			() => {
				const answers = {
					q1: { No: null }
				};
				expect( utils.getNextQuestionIndex( 1, questions, answers ) ).toBe( 3 );
			}
		);

		it( 'should return the correct index when dependency conditions are met',
			() => {
				const answers = {
					q1: { Yes: null },
					q4: { Maybe: null }
				};
				expect( utils.getNextQuestionIndex( 3, questions, answers ) ).toBe( 4 );
			}
		);
	} );
} );
