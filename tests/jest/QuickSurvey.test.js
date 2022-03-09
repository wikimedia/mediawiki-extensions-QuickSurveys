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
				yesButtonLabel: 'yes',
				noButtonLabel: 'no',
				question: 'question',
				thankYouMessage: 'thanks!',
				surveySessionToken: 'ss',
				pageviewToken: 'pv'
			}
		} );

		expect(
			survey.classes()
		).toContain( 'ext-quick-survey-panel' );

		expect(
			survey.findAllComponents( codex.CdxButton ).length
		).toBe( 1 );
	} );

	it( 'renders with required parameters', () => {
		const survey = VueTestUtils.mount( QuickSurvey, {
			propsData: {
				name: 'survey',
				yesButtonLabel: 'yes',
				noButtonLabel: 'no',
				question: 'question',
				thankYouMessage: 'thanks!',
				surveySessionToken: 'ss',
				pageviewToken: 'pv'
			}
		} );

		expect(
			survey.classes()
		).toContain( 'ext-quick-survey-panel' );

		expect(
			survey.findAllComponents( codex.CdxButton ).length
		).toBe( 1 );
	} );

	it( 'clicking the close button dismisses and removes survey', () => {
		const survey = VueTestUtils.mount( QuickSurvey, {
			propsData: {
				name: 'survey',
				question: 'question',
				thankYouMessage: 'thanks!',
				yesButtonLabel: 'yes please',
				noButtonLabel: 'no',
				externalLink: 'https://',
				surveySessionToken: 'ss',
				pageviewToken: 'pv'
			}
		} );

		return survey.findAllComponents( codex.CdxButton )[ 0 ].trigger( 'click' ).then( () => survey.vm.$nextTick() )
			.then( () => {
				expect(
					survey.emitted( 'dismiss' )
				).toBeTruthy();
				expect(
					survey.emitted( 'destroy' )
				).toBeTruthy();
			} );
	} );

	describe( 'ExternalSurvey', () => {
		it( 'renders two external survey buttons with externalLink', () => {
			const survey = VueTestUtils.mount( QuickSurvey, {
				propsData: {
					name: 'survey',
					question: 'question',
					thankYouMessage: 'thanks!',
					yesButtonLabel: 'yes',
					noButtonLabel: 'no',
					externalLink: 'https://',
					surveySessionToken: 'ss',
					pageviewToken: 'pv'
				}
			} );

			expect(
				survey.findAllComponents( codex.CdxButton ).length
			).toBe( 3 );
		} );

		it( 'clicking dismiss button removes survey', () => {
			const survey = VueTestUtils.mount( QuickSurvey, {
				propsData: {
					name: 'survey',
					question: 'question',
					thankYouMessage: 'thanks!',
					yesButtonLabel: 'yes please',
					noButtonLabel: 'no',
					externalLink: 'https://',
					surveySessionToken: 'ss',
					pageviewToken: 'pv'
				}
			} );

			return survey.findAllComponents( codex.CdxButton )[ 2 ].trigger( 'click' ).then( () => survey.vm.$nextTick() )
				.then( () => {
					expect(
						survey.emitted( 'dismiss' )
					).toBeTruthy();
					expect(
						survey.emitted( 'destroy' )
					).toBeTruthy();
				} );
		} );

		it( 'Opens window when yes clicked for external surveys', () => {
			const survey = VueTestUtils.mount( QuickSurvey, {
					propsData: {
						name: 'survey',
						thankYouMessage: 'thanks!',
						yesButtonLabel: 'yes',
						noButtonLabel: 'no',
						question: 'question',
						externalLink: 'https://',
						surveySessionToken: 'ss',
						pageviewToken: 'pv'
					}
				} ),
				buttons = survey.findAllComponents( codex.CdxButton );

			return buttons[ 1 ].trigger( 'click' ).then( () => {
				expect(
					window.open.mock.calls.length
				).toBe( 1 );
			} );
		} );

		it( 'displays privacy policy when completed if additional information is not defined', () => {
			const privacyPolicy = 'privacy policy instead of additional info';
			const survey = VueTestUtils.mount( QuickSurvey, {
					propsData: {
						name: 'survey',
						thankYouMessage: 'thanks!',
						footer: privacyPolicy,
						yesButtonLabel: 'yes',
						noButtonLabel: 'no',
						question: 'question',
						externalLink: 'https://',
						surveySessionToken: 'ss',
						pageviewToken: 'pv'
					}
				} ),
				buttons = survey.findAllComponents( codex.CdxButton );
			return buttons[ 1 ].trigger( 'click' ).then( () => {
				expect( survey.find( 'div.survey-footer' ).text() ).toContain( privacyPolicy );
			} );
		} );

		it( 'displays additional information instead of privacy policy when completed if additional information is defined', () => {
			const additionalInfo = 'addtional info instead of privacy policy';
			const privacyPolicy = 'privacy policy instead of additional info';
			const survey = VueTestUtils.mount( QuickSurvey, {
					propsData: {
						name: 'survey',
						thankYouMessage: 'thanks!',
						additionalInfo: additionalInfo,
						footer: privacyPolicy,
						yesButtonLabel: 'yes',
						noButtonLabel: 'no',
						question: 'question',
						externalLink: 'https://',
						surveySessionToken: 'ss',
						pageviewToken: 'pv'
					}
				} ),
				buttons = survey.findAllComponents( codex.CdxButton );
			return buttons[ 1 ].trigger( 'click' ).then( () => {
				expect( survey.find( 'div.survey-footer' ).text() ).toContain( additionalInfo );
			} );
		} );
	} );

	describe( 'SingleAnswerSurvey', () => {
		const additionalInfo = 'addtional info instead of privacy policy';
		const privacyPolicy = 'privacy policy instead of additional info';
		const SINGLE_ANSWER_SURVEY = {
			propsData: {
				layout: 'single-answer',
				name: 'survey',
				answers: [
					{ key: 'yes', label: 'Yes' },
					{ key: 'maybe', label: 'maybe' },
					{ key: 'no', label: 'no' }
				],
				footer: privacyPolicy,
				thankYouMessage: 'thank you come again',
				shuffleAnswersDisplay: true,
				freeformTextLabel: 'label-msg',
				instanceTokenParameterName: 'uniquetoken',
				question: 'question',
				isMobileLayout: false,
				surveySessionToken: 'ss',
				pageviewToken: 'pv'
			}
		};

		it( 'requires an answer', () => {
			const survey = VueTestUtils.mount( QuickSurvey, SINGLE_ANSWER_SURVEY );
			return survey.findAllComponents( codex.CdxButton )[ 4 ].trigger( 'click' ).then( () => {
				expect( window.alert.mock.calls.length ).toBe( 1 );
			} );
		} );

		it( 'does not shuffle answers when clicked', () => {
			const survey = VueTestUtils.mount( QuickSurvey, SINGLE_ANSWER_SURVEY );

			const buttons = survey.findAllComponents( codex.CdxButton );

			// get current text for each button
			const button1Text = buttons[ 1 ].text();
			const button2Text = buttons[ 2 ].text();
			const button3Text = buttons[ 3 ].text();

			// click the second choice
			return buttons[ 2 ].trigger( 'click' ).then( () => {
				expect( buttons[ 1 ].text() ).toBe( button1Text );
				expect( buttons[ 2 ].text() ).toBe( button2Text );
				expect( buttons[ 3 ].text() ).toBe( button3Text );
			} );
		} );

		it( 'Supports single answer surveys with free form text field', () => {
			const survey = VueTestUtils.mount( QuickSurvey, SINGLE_ANSWER_SURVEY );

			const buttons = survey.findAllComponents( codex.CdxButton );
			const checkboxes = survey.findAll( 'input[type="checkbox"]' );
			expect( checkboxes.length ).toBe( 0 );

			// choose "maybe"
			const maybeBtn = buttons[ 2 ];
			return maybeBtn.trigger( 'click' ).then( () => {
				const input = survey.findComponent( codex.CdxTextInput ).find( 'input' );
				// set value to freetext
				input.setValue( 'FREETEXT' );

				// nothing submitted at this point.
				expect( survey.emitted( 'logEvent' ) ).toBe( undefined );

				// submit.
				return buttons[ 4 ].trigger( 'click' ).then( () => {
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
							surveyCodeName: 'survey',
							surveyResponseValue: 'FREETEXT',
							surveySessionToken: 'ss',
							userLanguage: undefined
						}
					] );
				} );
			} );
		} );

		it( 'displays privacy policy when completed if additional information is not defined', () => {
			const survey = VueTestUtils.mount( QuickSurvey, SINGLE_ANSWER_SURVEY );
			const buttons = survey.findAllComponents( codex.CdxButton );
			return buttons[ 1 ].trigger( 'click' ).then( () => {
				expect( survey.find( 'div.survey-footer' ).text() ).toContain( privacyPolicy );
			} );
		} );

		it( 'displays additional information instead of privacy policy when completed if additional information is defined', () => {
			SINGLE_ANSWER_SURVEY.propsData.footer = additionalInfo;
			const survey = VueTestUtils.mount( QuickSurvey, SINGLE_ANSWER_SURVEY );
			const buttons = survey.findAllComponents( codex.CdxButton );
			return buttons[ 1 ].trigger( 'click' ).then( () => {
				expect( survey.find( 'div.survey-footer' ).text() ).toContain( additionalInfo );
			} );
		} );
	} );

	describe( 'MultipleAnswerSurvey', () => {
		const MULTI_ANSWER_SURVEY = {
			props: {
				layout: 'multiple-answer',
				name: 'survey-multi',
				answers: [
					{ key: 'A', label: 'Chinese' },
					{ key: 'B', label: 'French' },
					{ key: 'C', label: 'English' }
				],
				thankYouMessage: 'thank you come again',
				shuffleAnswersDisplay: true,
				freeformTextLabel: 'Other language',
				instanceTokenParameterName: 'uniquetoken',
				question: 'Which languages do you speak?',
				isMobileLayout: false,
				surveySessionToken: 'ss',
				pageviewToken: 'pv'
			}
		};

		it( 'Supports submitting one answer', () => {
			const survey = VueTestUtils.mount( QuickSurvey, MULTI_ANSWER_SURVEY );

			const checkboxes = survey.findAll( 'input[type="checkbox"]' );
			const submitButton = survey.findAllComponents( codex.CdxButton )[ 1 ];
			expect( checkboxes.length ).toBe( 3 );
			// Attempting to click the submit button without any selections will cause an alert
			return submitButton.trigger( 'click' ).then( () => {
				expect( window.alert.mock.calls.length ).toBe( 1 );
				expect( survey.emitted( 'logEvent' ) ).toBe( undefined );

				// However after clicking one of the checkboxes it should be possible to submit
				checkboxes[ 0 ].setChecked( true );
				expect( window.alert.mock.calls.length ).toBe( 1 );
				// clicking submit leads to the response being logged.
				return submitButton.trigger( 'click' ).then( () => {
					expect( survey.emitted( 'logEvent' ).length ).toBe( 1 );
				} );
			} );
		} );
	} );
} );
