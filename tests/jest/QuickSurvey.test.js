'use strict';

let QuickSurvey;
const VueTestUtils = require( '@vue/test-utils' );
const codex = require( '@wikimedia/codex' );

const open = window.open,
	alert = window.alert;

describe( 'QuickSurvey', () => {

	beforeEach( () => {
		QuickSurvey = require( '../../resources/ext.quicksurveys.lib/vue/QuickSurvey.vue' );
		window.open = jest.fn();
		mw.eventLog = {
			logEvent: jest.fn()
		};
		window.alert = jest.fn();
	} );

	afterEach( () => {
		window.open = open;
		window.alert = alert;
	} );

	it( 'renders with required parameters', () => {
		const survey = VueTestUtils.mount( QuickSurvey, {
			propsData: {
				name: 'survey',
				thankYouMessage: 'thanks!',
				surveySessionToken: 'ss',
				pageviewToken: 'pv',
				questions: [
					{
						name: 'question',
						question: 'question?',
						answers: [ { label: 'answer' } ]
					}
				]
			}
		} );

		expect(
			survey.classes()
		).toContain( 'ext-quick-survey-panel' );

		// Should find 2 buttons: close and submit
		expect(
			survey.findAllComponents( codex.CdxButton ).length
		).toBe( 2 );
	} );

	it( 'clicking the close button dismisses and removes survey', async () => {
		const survey = VueTestUtils.mount( QuickSurvey, {
			propsData: {
				name: 'survey',
				thankYouMessage: 'thanks!',
				surveySessionToken: 'ss',
				pageviewToken: 'pv',
				questions: [
					{
						name: 'question',
						question: 'question?',
						answers: [ { label: 'answer' } ]
					}
				]
			}
		} );

		await survey.findAllComponents( codex.CdxButton )[ 0 ].trigger( 'click' );
		await survey.vm.$nextTick();
		expect( survey.emitted( 'dismiss' ) ).toBeTruthy();
		expect( survey.emitted( 'destroy' ) ).toBeTruthy();
	} );

	describe( 'ExternalSurvey', () => {
		it( 'renders two external survey buttons with externalLink', () => {
			const survey = VueTestUtils.mount( QuickSurvey, {
				propsData: {
					name: 'survey',
					thankYouMessage: 'thanks!',
					surveySessionToken: 'ss',
					pageviewToken: 'pv',
					questions: [
						{
							name: 'question',
							question: 'question?',
							externalLink: 'https://',
							yesMsg: 'yes',
							noMsg: 'no'
						}
					]
				}
			} );

			expect(
				survey.findAllComponents( codex.CdxButton ).length
			).toBe( 3 );
		} );

		it( 'clicking dismiss button removes survey', async () => {
			const survey = VueTestUtils.mount( QuickSurvey, {
				propsData: {
					name: 'survey',
					thankYouMessage: 'thanks!',
					surveySessionToken: 'ss',
					pageviewToken: 'pv',
					questions: [
						{
							name: 'question',
							question: 'question?',
							externalLink: 'https://',
							yesMsg: 'yes',
							noMsg: 'no'
						}
					]
				}
			} );

			await survey.findAllComponents( codex.CdxButton )[ 1 ].trigger( 'click' );
			await survey.vm.$nextTick();
			expect( survey.emitted( 'dismiss' ) ).toBeTruthy();
			expect( survey.emitted( 'destroy' ) ).toBeTruthy();
		} );

		it( 'Opens window when yes clicked for external surveys', async () => {
			const survey = VueTestUtils.mount( QuickSurvey, {
					propsData: {
						name: 'survey',
						thankYouMessage: 'thanks!',
						surveySessionToken: 'ss',
						pageviewToken: 'pv',
						questions: [
							{
								name: 'question',
								question: 'question?',
								externalLink: 'https://',
								yesMsg: 'yes',
								noMsg: 'no'
							}
						]
					}
				} ),
				buttons = survey.findAllComponents( codex.CdxButton );

			await buttons[ 2 ].trigger( 'click' );
			expect( window.open.mock.calls.length ).toBe( 1 );
		} );

		it( 'displays privacy policy when completed if additional information is not defined',
			async () => {
				const privacyPolicy = 'privacy policy instead of additional info';
				const survey = VueTestUtils.mount( QuickSurvey, {
						propsData: {
							footer: privacyPolicy,
							name: 'survey',
							thankYouMessage: 'thanks!',
							surveySessionToken: 'ss',
							pageviewToken: 'pv',
							questions: [
								{
									name: 'question',
									question: 'question?',
									externalLink: 'https://',
									yesMsg: 'yes',
									noMsg: 'no'
								}
							]
						}
					} ),
					buttons = survey.findAllComponents( codex.CdxButton );

				await buttons[ 2 ].trigger( 'click' );
				expect( survey.find( 'div.survey-footer' ).text() ).toContain( privacyPolicy );
			}
		);

		it( 'displays additional information instead of privacy policy when completed if additional information is defined',
			async () => {
				const additionalInfo = 'additional info instead of privacy policy';
				const privacyPolicy = 'privacy policy instead of additional info';
				const survey = VueTestUtils.mount( QuickSurvey, {
						propsData: {
							additionalInfo: additionalInfo,
							footer: privacyPolicy,
							name: 'survey',
							thankYouMessage: 'thanks!',
							surveySessionToken: 'ss',
							pageviewToken: 'pv',
							questions: [
								{
									name: 'question',
									question: 'question?',
									externalLink: 'https://',
									yesMsg: 'yes',
									noMsg: 'no'
								}
							]
						}
					} ),
					buttons = survey.findAllComponents( codex.CdxButton );

				await buttons[ 2 ].trigger( 'click' );
				expect( survey.find( 'div.survey-footer' ).text() ).toContain( additionalInfo );
			}
		);
	} );

	describe( 'SingleAnswerSurvey', () => {
		const additionalInfo = 'additional info instead of privacy policy';
		const privacyPolicy = 'privacy policy instead of additional info';
		const SINGLE_ANSWER_SURVEY = {
			propsData: {
				name: 'survey',
				footer: privacyPolicy,
				thankYouMessage: 'thank you come again',
				isMobileLayout: false,
				surveySessionToken: 'ss',
				pageviewToken: 'pv',
				questions: [
					{
						name: 'question',
						layout: 'single-answer',
						questionKey: 'questionKey',
						question: 'question?',
						shuffleAnswersDisplay: true,
						answers: [
							{ key: 'yes', label: 'yes' },
							{ key: 'maybe', label: 'maybe', freeformTextLabel: 'maybe?' },
							{ key: 'no', label: 'no' }
						]
					}
				]
			}
		};

		it( 'requires an answer', async () => {
			const survey = VueTestUtils.mount( QuickSurvey, SINGLE_ANSWER_SURVEY );
			await survey.findAllComponents( codex.CdxButton )[ 1 ].trigger( 'click' );
			expect( window.alert.mock.calls.length ).toBe( 1 );
		} );

		it( 'does not shuffle answers when clicked', async () => {
			const survey = VueTestUtils.mount( QuickSurvey, SINGLE_ANSWER_SURVEY );

			const buttons = survey.findAllComponents( codex.CdxRadio );

			// get current text for each button
			const button1Text = buttons[ 0 ].text();
			const button2Text = buttons[ 1 ].text();
			const button3Text = buttons[ 2 ].text();

			// click the second choice
			await buttons[ 1 ].trigger( 'click' );
			expect( buttons[ 0 ].text() ).toBe( button1Text );
			expect( buttons[ 1 ].text() ).toBe( button2Text );
			expect( buttons[ 2 ].text() ).toBe( button3Text );
		} );

		it( 'Supports single answer surveys with free form text field', async () => {
			const props = Object.assign( {}, SINGLE_ANSWER_SURVEY );
			props.propsData.questions[ 0 ].shuffleAnswersDisplay = false;
			const survey = VueTestUtils.mount( QuickSurvey, props );

			const buttons = survey.findAllComponents( codex.CdxButton );
			const radioButtons = survey.findAllComponents( codex.CdxRadio );
			const checkboxes = survey.findAll( 'input[type="checkbox"]' );
			expect( checkboxes.length ).toBe( 0 );

			// choose "maybe"
			const maybeBtn = radioButtons[ 1 ];
			await maybeBtn.find( 'input' ).setValue( 'checked' );

			const input = survey.findComponent( codex.CdxTextInput ).find( 'input' );
			// set value to freetext
			await input.setValue( 'FREETEXT' );

			// nothing submitted at this point.
			expect( survey.emitted( 'logEvent' ) ).toBe( undefined );

			// submit.
			await buttons[ 1 ].trigger( 'click' );
			const logEvent = survey.emitted( 'logEvent' );
			expect( logEvent.length ).toBe( 1 );
			expect(
				logEvent[ 0 ]
			).toStrictEqual( [
				'QuickSurveysResponses',
				{
					countryCode: 'Unknown',
					isLoggedIn: true,
					isTablet: true,
					namespaceId: undefined,
					pageId: undefined,
					pageTitle: undefined,
					pageviewToken: 'pv',
					platform: 'web',
					skin: undefined,
					surveyAnswers: [ 'maybe' ],
					surveyCodeName: 'survey',
					surveyQuestionLabel: 'questionKey',
					surveyResponseFreeText: { maybe: 'FREETEXT' },
					surveyResponseValue: 'maybe',
					surveySessionToken: 'ss',
					userLanguage: undefined
				}
			] );
		} );

		it( 'displays privacy policy when completed if additional information is not defined',
			async () => {
				const survey = VueTestUtils.mount( QuickSurvey, SINGLE_ANSWER_SURVEY );
				const buttons = survey.findAllComponents( codex.CdxButton );
				await buttons[ 1 ].trigger( 'click' );
				expect( survey.find( 'div.survey-footer' ).text() ).toContain( privacyPolicy );
			}
		);

		it( 'displays additional information instead of privacy policy when completed if additional information is defined',
			async () => {
				SINGLE_ANSWER_SURVEY.propsData.footer = additionalInfo;
				const survey = VueTestUtils.mount( QuickSurvey, SINGLE_ANSWER_SURVEY );
				const buttons = survey.findAllComponents( codex.CdxButton );
				await buttons[ 1 ].trigger( 'click' );
				expect( survey.find( 'div.survey-footer' ).text() ).toContain( additionalInfo );
			}
		);
	} );

	describe( 'MultipleAnswerSurvey', () => {
		const MULTI_ANSWER_SURVEY = {
			props: {
				name: 'survey-multi',
				thankYouMessage: 'thank you come again',
				surveySessionToken: 'ss',
				pageviewToken: 'pv',
				questions: [
					{
						name: 'question',
						layout: 'multiple-answer',
						questionKey: 'questionKey',
						question: 'Which languages do you speak?',
						answers: [
							{ key: 'A', label: 'Chinese' },
							{ key: 'B', label: 'French' },
							{ key: 'C', label: 'English' },
							{ key: 'D', label: 'Other', freeformTextLabel: 'Please specify' }
						]
					}
				]
			}
		};

		it( 'Supports submitting one answer', async () => {
			const survey = VueTestUtils.mount( QuickSurvey, MULTI_ANSWER_SURVEY );

			const checkboxes = survey.findAll( 'input[type="checkbox"]' );
			const submitButton = survey.findAllComponents( codex.CdxButton )[ 1 ];
			expect( checkboxes.length ).toBe( 4 );
			// Attempting to click the submit button without any selections will cause an alert
			await submitButton.trigger( 'click' );
			expect( window.alert.mock.calls.length ).toBe( 1 );
			expect( survey.emitted( 'logEvent' ) ).toBe( undefined );

			// However after clicking one of the checkboxes it should be possible to submit
			await checkboxes[ 0 ].setChecked( true );
			expect( window.alert.mock.calls.length ).toBe( 1 );
			// clicking submit leads to the response being logged.
			await submitButton.trigger( 'click' );
			expect( survey.emitted( 'logEvent' ).length ).toBe( 1 );
		} );

		it( 'Supports free text input for checkbox', async () => {
			const survey = VueTestUtils.mount( QuickSurvey, MULTI_ANSWER_SURVEY );
			expect(
				survey.findAllComponents( codex.CdxTextInput ).length
			).toBe( 0 );

			const checkboxes = survey.findAll( 'input[type="checkbox"]' );
			await checkboxes[ 3 ].setChecked( true );
			expect(
				survey.findAllComponents( codex.CdxTextInput ).length
			).toBe( 1 );
		} );
	} );

	describe( 'Multiple questions survey', () => {
		const MULTIPLE_QUESTIONS_SURVEY = {
			propsData: {
				name: 'survey-multi-questions',
				footer: 'privacy policy instead of additional info',
				thankYouMessage: 'thank you come again',
				isMobileLayout: false,
				surveySessionToken: 'ss',
				pageviewToken: 'pv',
				backButtonLabel: 'BackButtonLabel',
				questions: [
					{
						name: 'question 1',
						layout: 'single-answer',
						questionKey: 'questionKey1',
						question: 'Is this question 1?',
						answers: [
							{ key: 'yes', label: 'yes' },
							{ key: 'maybe', label: 'maybe' },
							{ key: 'no', label: 'no' }
						]
					},
					{
						name: 'question 2',
						layout: 'multiple-answer',
						questionKey: 'questionKey2',
						question: 'Which languages do you speak?',
						answers: [
							{ key: 'A', label: 'Chinese' },
							{ key: 'B', label: 'French' },
							{ key: 'C', label: 'English' }
						]
					}
				]
			}
		};

		it( 'Supports multiple questions in the same survey', async () => {
			const survey = VueTestUtils.mount( QuickSurvey, MULTIPLE_QUESTIONS_SURVEY );

			const buttons = survey.findAllComponents( codex.CdxButton );
			const radioButtons = survey.findAllComponents( codex.CdxRadio );

			// choose "maybe"
			const maybeBtn = radioButtons[ 1 ];
			await maybeBtn.find( 'input' ).setValue( 'checked' );
			const expectedLogEventValues = {
				countryCode: 'Unknown',
				isLoggedIn: true,
				isTablet: true,
				namespaceId: undefined,
				pageId: undefined,
				pageTitle: undefined,
				pageviewToken: 'pv',
				platform: 'web',
				skin: undefined,
				surveyAnswers: [ 'maybe' ],
				surveyCodeName: 'survey-multi-questions',
				surveyQuestionLabel: 'questionKey1',
				surveyResponseFreeText: {},
				surveyResponseValue: 'maybe',
				surveySessionToken: 'ss',
				userLanguage: undefined
			};
			// nothing submitted at this point.
			expect( survey.emitted( 'logEvent' ) ).toBe( undefined );

			// submit first question
			await buttons[ 1 ].trigger( 'click' );
			const logEvent = survey.emitted( 'logEvent' );
			expect( logEvent.length ).toBe( 1 );
			expect( logEvent[ 0 ] ).toStrictEqual(
				[ 'QuickSurveysResponses', expectedLogEventValues ]
			);

			const checkboxes = survey.findAll( 'input[type="checkbox"]' );
			await checkboxes[ 0 ].setChecked( true );

			// submit second question
			await buttons[ 1 ].trigger( 'click' );
			expectedLogEventValues.surveyQuestionLabel = 'questionKey2';
			expectedLogEventValues.surveyResponseValue = '';
			expectedLogEventValues.surveyAnswers = [ 'A' ];
			expectedLogEventValues.surveyResponseValue = 'A';
			const logEvent2 = survey.emitted( 'logEvent' );
			expect( logEvent2.length ).toBe( 2 );
			expect( logEvent2[ 1 ] ).toStrictEqual(
				[ 'QuickSurveysResponses', expectedLogEventValues ]
			);
		} );

		it( 'should display previous question when clicking on back button',
			async () => {
				const survey = VueTestUtils.mount( QuickSurvey, MULTIPLE_QUESTIONS_SURVEY );
				const radioButtons = survey.findAllComponents( codex.CdxRadio );

				// choose "maybe"
				const maybeBtn = radioButtons[ 1 ];
				await maybeBtn.find( 'input' ).setValue( 'checked' );
				const submitButton = survey.findAllComponents( codex.CdxButton )[ 1 ];
				// submit first question
				await submitButton.trigger( 'click' );
				// should find the text for question 2
				expect( survey.html() ).toContain( 'Which languages do you speak?' );

				// back button is the second button now, it renders before the submit
				const backButton = survey.findAllComponents( codex.CdxButton )[ 1 ];
				expect( backButton.text() ).toBe( 'BackButtonLabel' );

				await backButton.trigger( 'click' );
				// should find the text for the question 1
				expect( survey.html() ).toContain( 'Is this question 1?' );
			}
		);
	} );

	describe( 'End of survey', () => {
		const SINGLE_ANSWER_SURVEY = {
			props: {
				name: 'survey-single',
				thankYouMessage: 'thank you come again',
				thankYouDescription: 'description thank you message',
				surveySessionToken: 'ss',
				pageviewToken: 'pv',
				questions: [
					{
						name: 'question',
						layout: 'single-answer',
						questionKey: 'questionKey',
						question: 'Choose one language',
						answers: [
							{ key: 'A', label: 'Chinese' },
							{ key: 'B', label: 'French' },
							{ key: 'C', label: 'English' },
							{ key: 'D', label: 'Other', freeformTextLabel: 'Please specify' }
						]
					}
				]
			}
		};

		it( 'Should contain thank you description text', async () => {
			const survey = VueTestUtils.mount( QuickSurvey, SINGLE_ANSWER_SURVEY );

			// initially, the survey should NOT contain the thank you description
			expect( survey.text() ).not.toContain( 'description thank you message' );

			const radioButtons = survey.findAllComponents( codex.CdxRadio );
			// select first option
			await radioButtons[ 0 ].find( 'input' ).setValue( 'checked' );

			const submitButton = survey.findAllComponents( codex.CdxButton )[ 1 ];
			await submitButton.trigger( 'click' );

			// now it should contain the thank you description
			expect( survey.text() ).toContain( 'description thank you message' );
		} );
	} );
} );
