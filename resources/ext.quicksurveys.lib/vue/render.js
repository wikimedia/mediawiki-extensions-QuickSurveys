/* eslint-disable jsdoc/no-undefined-types */
var QuickSurvey = require( './QuickSurvey.vue' );

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
	var deferred = $.Deferred();
	var h = Vue.h;
	var vm = Vue.createMwApp( {
		mounted: function () {
			deferred.resolve( this.$el );
		},
		/**
		 * @return {Vue}
		 */
		render: function () {
			// eslint-disable-next-line mediawiki/msg-doc
			var externalLink = survey.link ? new mw.Uri( mw.message( survey.link ).parse() ) : '';

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
					yesButtonLabel: mw.msg( 'ext-quicksurveys-external-survey-yes-button' ),
					noButtonLabel: mw.msg( 'ext-quicksurveys-external-survey-no-button' ),
					additionalInfo: survey.additionalInfo ?
						// eslint-disable-next-line mediawiki/msg-doc
						mw.message( survey.additionalInfo ).parse() : undefined,
					// eslint-disable-next-line mediawiki/msg-doc
					thankYouMessage: mw.msg( survey.confirmMsg || 'ext-quicksurveys-survey-confirm-msg' ),
					// eslint-disable-next-line mediawiki/msg-doc
					footer: mw.message(
						survey.privacyPolicy || 'ext-quicksurveys-survey-privacy-policy-default-text'
					).parse(),
					shuffleAnswersDisplay: survey.shuffleAnswersDisplay,
					freeformTextLabel: survey.freeformTextLabel ?
						// eslint-disable-next-line mediawiki/msg-doc
						mw.msg( survey.freeformTextLabel ) : undefined,
					layout: survey.layout,
					// eslint-disable-next-line mediawiki/msg-doc
					question: mw.msg( survey.question ),
					answers: ( survey.answers || [] ).map( function ( key ) {
						return {
							key: key,
							// eslint-disable-next-line mediawiki/msg-doc
							label: mw.msg( key )
						};
					} ),
					name: survey.name,
					externalLink: externalLink.toString(),
					// eslint-disable-next-line mediawiki/msg-doc
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
	panel.setAttribute( 'class', '' );
	panel.innerHTML = '';
	vm.mount( panel );
	return deferred;
}

module.exports = {
	render: render
};
