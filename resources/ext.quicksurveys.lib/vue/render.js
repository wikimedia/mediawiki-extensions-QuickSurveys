/* eslint-disable jsdoc/no-undefined-types */
const QuickSurvey = require( './QuickSurvey.vue' ),
	utils = require( './utils.js' );

/**
 * @param {Vue} Vue library
 * @param {Element} panel to render into
 * @param {SurveyDefinition} survey
 * @param {Function} dismissSurvey to call when a survey is dismissed.
 * @param {string} surveySessionToken
 * @param {string} pageviewToken
 * @param {boolean} isMobileLayout
 * @param {string} htmlDirection
 * @param {Function} logEvent event logging function
 * @return {jQuery.Deferred}
 */
function render(
	Vue,
	panel, survey, dismissSurvey, surveySessionToken, pageviewToken, isMobileLayout,
	htmlDirection, logEvent
) {
	const deferred = $.Deferred();
	const h = Vue.h;
	const vm = Vue.createMwApp( {
		compatConfig: {
			MODE: 3
		},
		mounted: function () {
			deferred.resolve( this.$el );
		},
		/**
		 * @return {Vue}
		 */
		render: function () {
			const questions = utils.processSurveyQuestions( survey.questions, pageviewToken );

			return h( QuickSurvey,
				{
					onLogEvent: logEvent,
					onDismiss: dismissSurvey,
					onDestroy: function () {
						vm.unmount();
					},
					submitButtonLabel: mw.msg( 'ext-quicksurveys-internal-freeform-survey-submit-button' ),
					noAnswerErrorMessage: mw.msg( 'ext-quicksurveys-internal-freeform-survey-no-answer-alert' ),
					additionalInfo: survey.additionalInfo ?
						// eslint-disable-next-line mediawiki/msg-doc
						mw.message( survey.additionalInfo ).parse() : undefined,
					// eslint-disable-next-line mediawiki/msg-doc
					thankYouMessage: mw.message( survey.confirmMsg || 'ext-quicksurveys-survey-confirm-msg' ).parse(),
					thankYouDescription: survey.confirmDescription ?
						// eslint-disable-next-line mediawiki/msg-doc
						mw.message( survey.confirmDescription ).parse() : null,
					// eslint-disable-next-line mediawiki/msg-doc
					footer: mw.message(
						survey.privacyPolicy || 'ext-quicksurveys-survey-privacy-policy-default-text'
					).parse(),
					name: survey.name,
					surveySessionToken: surveySessionToken,
					pageviewToken: pageviewToken,
					isMobileLayout: isMobileLayout,
					direction: htmlDirection,
					backButtonLabel: mw.msg( 'ext-quicksurveys-internal-freeform-survey-back-button' ),
					questions: questions,
					surveyPreferencesDisclaimer: mw.message(
						'ext-quicksurveys-survey-change-preferences-disclaimer'
					).parse()
				}
			);
		}
	} );
	// disable spinner.
	panel.removeAttribute( 'class' );
	panel.removeAttribute( 'title' );
	panel.innerHTML = '';
	vm.mount( panel );
	return deferred;
}

module.exports = {
	render: render
};
