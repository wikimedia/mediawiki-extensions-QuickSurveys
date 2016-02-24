( function ( $ ) {
	var isMainPage = mw.config.get( 'wgIsMainPage' ),
		$bodyContent = $( '#bodyContent' ),
		isMobileLayout = window.innerWidth <= 768,
		isArticle = mw.config.get( 'wgIsArticle' ),
		// See https://developer.mozilla.org/en-US/docs/Web/API/Navigator/doNotTrack
		// Taken from https://www.npmjs.com/package/dnt-polyfill
		isDntEnabled = window.doNotTrack === '1' ||
			window.navigator && (
				window.navigator.doNotTrack === '1' ||
				window.navigator.doNotTrack === 'yes' ||
				window.navigator.msDoNotTrack === '1'
			);

	// Do nothing when not on an article or the user doesn't want to be tracked
	if ( isMainPage || !isArticle || isDntEnabled ) {
		return;
	}

	// make sure the beta opt-in panel is not shown in Minerva
	if ( mw.config.get( 'skin' ) === 'minerva' ) {
		mw.trackSubscribe( 'minerva.betaoptin', function ( topic, data ) {
			if ( data.isPanelShown === false ) {
				mw.extQuickSurveys.showSurvey( $bodyContent, isMobileLayout );
			}
		} );
	} else {
		mw.extQuickSurveys.showSurvey( $bodyContent, isMobileLayout );
	}

}( jQuery ) );
