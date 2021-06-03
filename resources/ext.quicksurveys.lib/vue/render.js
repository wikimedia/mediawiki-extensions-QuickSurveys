/* eslint-disable jsdoc/no-undefined-types */
var Vue = require( 'vue' ),
	QuickSurvey = require( './QuickSurvey.vue' );

/**
 * @param {Element} panel to render into
 * eslint-disable-next-line jsdoc/no-undefined-types
 * @param {SurveyDefinition} survey
 * @param {Function} dismissSurvey to call when a survey is dismissed.
 * @param {string} surveySessionToken
 * @param {string} pageviewToken
 * @param {boolean} isMobileLayout
 * @return {jQuery.Deferred}
 */
function render(
	panel, survey, dismissSurvey, surveySessionToken, pageviewToken, isMobileLayout
) {
	var deferred = $.Deferred();
	// eslint-disable-next-line no-new
	new Vue( {
		el: panel,
		mounted: function () {
			deferred.resolve( this.$el );
		},
		/**
		 * @param {Function} createElement
		 * @return {Vue}
		 */
		render: function ( createElement ) {
			// eslint-disable-next-line mediawiki/msg-doc
			var externalLink = survey.link ? new mw.Uri( mw.message( survey.link ).parse() ) : '';

			if ( externalLink && survey.instanceTokenParameterName ) {
				externalLink.query[ survey.instanceTokenParameterName ] = pageviewToken;
			}

			return createElement( QuickSurvey, {
				on: {
					dismiss: dismissSurvey
				},
				props: {
					submitButtonLabel: mw.msg( 'ext-quicksurveys-internal-freeform-survey-submit-button' ),
					noAnswerErrorMessage: mw.msg( 'ext-quicksurveys-internal-freeform-survey-no-answer-alert' ),
					yesButtonLabel: mw.msg( 'ext-quicksurveys-external-survey-yes-button' ),
					noButtonLabel: mw.msg( 'ext-quicksurveys-external-survey-no-button' ),
					thankYouMessage: mw.msg( 'ext-quicksurveys-survey-confirm-msg' ),
					footer: mw.message( 'ext-quicksurveys-survey-privacy-policy-default-text' ).parse(),
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
					isMobileLayout: isMobileLayout
				}
			} );
		}
	} );

	return deferred;
}

module.exports = {
	render: render
};
