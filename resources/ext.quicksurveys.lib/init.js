var QuickSurveyLib = require( './lib.js' ),
	overrideParam = mw.util.getParamValue( 'quicksurvey' );

QuickSurveyLib.showSurvey( overrideParam );

// TODO: Deprecate and remove once usages in other repos are updated.
mw.extQuickSurveys = QuickSurveyLib;

module.exports = {
	test: {
		ExternalSurvey: require( './views/ExternalSurvey.js' ),
		MultipleAnswerSurvey: require( './views/MultipleAnswerSurvey.js' ),
		SingleAnswerSurvey: require( './views/SingleAnswerSurvey.js' ),
		insertPanel: QuickSurveyLib.test.insertPanel,
		isInAudience: QuickSurveyLib.test.isInAudience,
		surveyMatchesPlatform: QuickSurveyLib.test.surveyMatchesPlatform
	}
};
