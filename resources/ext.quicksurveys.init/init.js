const forcedSurvey = mw.util.getParamValue( 'quicksurvey' );

require( 'ext.quicksurveys.lib' ).showSurvey(
	forcedSurvey,
	// where to render survey
	null,
	// force display of surveys where query string was used
	!!forcedSurvey
);
