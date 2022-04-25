var QuickSurveyLib = require( './lib.js' ),
	overrideParam = mw.util.getParamValue( 'quicksurvey' );

QuickSurveyLib.showSurvey( overrideParam );

// TODO: Deprecate and remove once usages in other repos are updated.
mw.extQuickSurveys = QuickSurveyLib;

module.exports = QuickSurveyLib;
