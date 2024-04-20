/* eslint-disable jsdoc/no-undefined-types */
const QuickSurvey = require( './QuickSurvey.vue' );

/**
 * @param {Vue} Vue library
 * @param {Element} panel to render into
 * eslint-disable-next-line jsdoc/no-undefined-types
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
			// eslint-disable-next-line mediawiki/msg-doc
			const externalLink = survey.link ? new mw.Uri( mw.message( survey.link ).parse() ) : '';

			if ( externalLink && survey.instanceTokenParameterName ) {
				externalLink.query[ survey.instanceTokenParameterName ] = pageviewToken;
			}

			return h( QuickSurvey,
				{
					onLogEvent: logEvent,
					onDismiss: dismissSurvey,
					onDestroy: function () {
						vm.unmount();
					},
					submitButtonLabel: mw.msg( 'ext-quicksurveys-internal-freeform-survey-submit-button' ),
					noAnswerErrorMessage: mw.msg( 'ext-quicksurveys-internal-freeform-survey-no-answer-alert' ),

					// * ext-quicksurveys-external-survey-yes-button
					// * Message key supplied by survey author
					yesButtonLabel: survey.yesMsg ? mw.msg( survey.yesMsg ) : '',

					// * ext-quicksurveys-external-survey-no-button
					// * Message key supplied by survey author
					noButtonLabel: survey.noMsg ? mw.msg( survey.noMsg ) : '',

					additionalInfo: survey.additionalInfo ?
						mw.message( survey.additionalInfo ).parse() : undefined,
					thankYouMessage: mw.msg( survey.confirmMsg || 'ext-quicksurveys-survey-confirm-msg' ),
					footer: mw.message(
						survey.privacyPolicy || 'ext-quicksurveys-survey-privacy-policy-default-text'
					).parse(),
					shuffleAnswersDisplay: survey.shuffleAnswersDisplay,
					freeformTextLabel: survey.freeformTextLabel ?
						mw.msg( survey.freeformTextLabel ) : undefined,
					layout: survey.layout,
					question: mw.msg( survey.question ),
					answers: ( survey.answers || [] ).map( function ( key ) {
						return {
							key: key,
							label: mw.msg( key )
						};
					} ),
					name: survey.name,
					externalLink: externalLink.toString(),
					description: survey.description ? mw.msg( survey.description ) : '',
					surveySessionToken: surveySessionToken,
					pageviewToken: pageviewToken,
					isMobileLayout: isMobileLayout,
					direction: htmlDirection
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
