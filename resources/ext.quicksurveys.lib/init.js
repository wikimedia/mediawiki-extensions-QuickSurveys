( function () {
	// The following tests should be kept in sync with the mw.eventLog.sendBeacon definition in
	// https://gerrit.wikimedia.org/g/mediawiki/extensions/EventLogging/+/master/modules/ext.eventLogging/core.js.
	if (
		/1|yes/.test( navigator.doNotTrack ) || // Support: Firefox < 32 (yes/no)
		window.doNotTrack === '1' // Support: IE 11, Safari 7.1.3+ (window.doNotTrack)
	) {
		return;
	}

	mw.extQuickSurveys.showSurvey( mw.util.getParamValue( 'quicksurvey' ) );
}() );