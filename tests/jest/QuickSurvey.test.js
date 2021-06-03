let QuickSurvey;
const VueTestUtils = require( '@vue/test-utils' );
const wvui = require( '@wikimedia/wvui' ).default;
const QuickSurveyHelpers = require( '../../resources/ext.quicksurveys.lib/QuickSurveyUtils.js' );

const open = window.open,
	logResponse = QuickSurveyHelpers.logResponse,
	alert = window.alert;

describe( 'QuickSurvey', () => {

	beforeEach( () => {
		jest.mock( 'wvui', () => wvui );
		QuickSurvey = require( '../../resources/ext.quicksurveys.lib/vue/QuickSurvey.vue' );
		window.open = jest.fn();
		mw.eventLog = {
			logEvent: jest.fn()
		};
		window.alert = jest.fn();
		QuickSurveyHelpers.logResponse = jest.fn();
	} );

	afterEach( () => {
		window.open = open;
		QuickSurveyHelpers.logResponse = logResponse;
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
			survey.findComponent( wvui.WvuiButton ).exists()
		).toBe( false );
	} );

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
			survey.findAllComponents( wvui.WvuiButton ).length
		).toBe( 2 );
	} );

	it( 'clicking dismiss button ends survey', () => {
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

		return survey.findAllComponents( wvui.WvuiButton ).at( 1 ).trigger( 'click' ).then( () => {
			expect(
				survey.html()
			).toContain( 'thanks!' );
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
			buttons = survey.findAllComponents( wvui.WvuiButton );

		return buttons.at( 0 ).trigger( 'click' ).then( () => {
			expect(
				window.open.mock.calls.length
			).toBe( 1 );
		} );
	} );

	describe( 'SingleAnswerSurvey', () => {
		const SINGLE_ANSWER_SURVEY = {
			propsData: {
				layout: 'single-answer',
				name: 'survey',
				answers: [
					{ key: 'yes', label: 'Yes' },
					{ key: 'maybe', label: 'maybe' },
					{ key: 'no', label: 'no' }
				],
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
			return survey.findAllComponents( wvui.WvuiButton ).at( 3 ).trigger( 'click' ).then( () => {
				expect( window.alert.mock.calls.length ).toBe( 1 );
			} );
		} );

		it( 'Supports single answer surveys with free form text field', () => {
			const survey = VueTestUtils.mount( QuickSurvey, SINGLE_ANSWER_SURVEY );

			const buttons = survey.findAllComponents( wvui.WvuiButton );
			const checkboxes = survey.findAll( 'input[type="checkbox"]' );
			expect( checkboxes.length ).toBe( 0 );

			// choose "maybe"
			const maybeBtn = buttons.at( 1 );
			return maybeBtn.trigger( 'click' ).then( () => {
				const input = survey.findComponent( wvui.WvuiInput ).find( 'input' );
				// set value to freetext
				input.setValue( 'FREETEXT' );

				// nothing submitted at this point.
				expect( QuickSurveyHelpers.logResponse.mock.calls.length ).toBe( 0 );

				// submit.
				return buttons.at( 3 ).trigger( 'click' ).then( () => {
					expect( QuickSurveyHelpers.logResponse.mock.calls.length ).toBe( 1 );
					expect(
						QuickSurveyHelpers.logResponse
					).toHaveBeenCalledWith(
						'survey',
						'FREETEXT',
						'ss',
						'pv',
						true
					);
				} );
			} );
		} );
	} );

	describe( 'MultipleAnswerSurvey', () => {
		const MULTI_ANSWER_SURVEY = {
			propsData: {
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
			const submitButton = survey.findAllComponents( wvui.WvuiButton ).at( 0 );
			expect( checkboxes.length ).toBe( 3 );
			// Attempting to click the submit button without any selections will cause an alert
			return submitButton.trigger( 'click' ).then( () => {
				expect( window.alert.mock.calls.length ).toBe( 1 );
				expect( QuickSurveyHelpers.logResponse.mock.calls.length ).toBe( 0 );

				// However after clicking one of the checkboxes it should be possible to submit
				checkboxes.at( 0 ).setChecked( true );
				expect( window.alert.mock.calls.length ).toBe( 1 );
				// clicking submit leads to the response being logged.
				return submitButton.trigger( 'click' ).then( () => {
					expect( QuickSurveyHelpers.logResponse.mock.calls.length ).toBe( 1 );
				} );
			} );
		} );
	} );
} );
