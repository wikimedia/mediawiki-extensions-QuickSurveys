const forcedSurvey = mw.util.getParamValue( 'quicksurvey' );

require( 'ext.quicksurveys.lib' ).showSurvey(
	forcedSurvey,
	// where to render survey
	null,
	// force display of surveys where query string was used
	!!forcedSurvey,
	// by default include sensitive data as surveys defined in config
	// are assumed to be safe since they cannot be targeted in a way
	// that leaks information about the user.
	true
);
