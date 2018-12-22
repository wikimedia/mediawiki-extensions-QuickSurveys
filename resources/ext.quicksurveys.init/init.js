( function () {
	var isMainPage = mw.config.get( 'wgIsMainPage' ),
		isArticle = mw.config.get( 'wgIsArticle' ),
		// See https://developer.mozilla.org/en-US/docs/Web/API/Navigator/doNotTrack
		// Should match the logic in EventLogging/core.js to make sure we show the
		// survey under the same circumtances that allow us to log its result.
		isDntEnabled = window.doNotTrack === '1' || (
			navigator.doNotTrack === '1' ||
			navigator.doNotTrack === 'yes' ||
			navigator.msDoNotTrack === '1'
		),
		forcedSurvey = mw.util.getParamValue( 'quicksurvey' );

	// Do nothing when not on an article or the user doesn't want to be tracked
	if ( isMainPage || !isArticle || isDntEnabled ) {
		return;
	}

	// make sure the beta opt-in panel is not shown in Minerva
	if ( mw.config.get( 'skin' ) === 'minerva' ) {
		mw.trackSubscribe( 'mobile.betaoptin', function ( topic, data ) {
			if ( data.isPanelShown === false ) {
				mw.extQuickSurveys.showSurvey( forcedSurvey );
			}
		} );
	} else {
		mw.extQuickSurveys.showSurvey( forcedSurvey );
	}

}() );
