var QuickSurveyLib = require( './lib.js' ),
	overrideParam = mw.util.getParamValue( 'quicksurvey' );

QuickSurveyLib.showSurvey( overrideParam );

// TODO: Deprecate and remove once usages in other repos are updated.
mw.extQuickSurveys = QuickSurveyLib;

module.exports = {
	private: {
		ExternalSurvey: require( './views/ExternalSurvey.js' ),
		MultipleAnswerSurvey: require( './views/MultipleAnswerSurvey.js' ),
		SingleAnswerSurvey: require( './views/SingleAnswerSurvey.js' ),
		insertPanel: QuickSurveyLib.private.insertPanel,
		isInAudience: QuickSurveyLib.private.isInAudience,
		surveyMatchesPlatform: QuickSurveyLib.private.surveyMatchesPlatform
	}
};
