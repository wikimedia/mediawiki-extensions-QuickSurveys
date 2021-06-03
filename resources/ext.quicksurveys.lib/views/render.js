/* eslint-disable jsdoc/no-undefined-types */
var ExternalSurvey = require( './ExternalSurvey.js' ),
	SingleAnswerSurvey = require( './SingleAnswerSurvey.js' ),
	MultipleAnswerSurvey = require( './MultipleAnswerSurvey.js' );

/**
 * @param {Element} element
 * @param {SurveyDefinition} survey
 * @param {Function} dismissSurvey to call when a survey is dismissed.
 * @param {string} surveySessionToken
 * @param {string} pageviewToken
 * @param {boolean} isMobileLayout
 * @return {jQuery.Deferred}
 */
function render(
	element, survey, dismissSurvey, surveySessionToken, pageviewToken, isMobileLayout
) {
	var panel,
		deferred = $.Deferred(),
		options = {
			survey: survey,
			templateData: {
				// eslint-disable-next-line mediawiki/msg-doc
				question: mw.msg( survey.question ),
				// eslint-disable-next-line mediawiki/msg-doc
				description: survey.description ? mw.msg( survey.description ) : ''
			},
			surveySessionToken: surveySessionToken,
			pageviewToken: pageviewToken,
			isMobileLayout: isMobileLayout
		};

	if ( survey.type === 'external' ) {
		panel = new ExternalSurvey( options );
	} else if ( survey.layout === 'single-answer' ) {
		panel = new SingleAnswerSurvey( options );
	} else if ( survey.layout === 'multiple-answer' ) {
		panel = new MultipleAnswerSurvey( options );
	} else {
		return deferred.reject();
	}

	panel.on( 'dismiss', dismissSurvey );
	$( element ).replaceWith( panel.$element );

	return deferred.resolve();
}

module.exports = {
	render: render,
	test: {
		ExternalSurvey: ExternalSurvey,
		MultipleAnswerSurvey: MultipleAnswerSurvey,
		SingleAnswerSurvey: SingleAnswerSurvey
	}
};
