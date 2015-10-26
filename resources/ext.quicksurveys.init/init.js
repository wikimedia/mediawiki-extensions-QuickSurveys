( function ( $ ) {
	var isMainPage = mw.config.get( 'wgIsMainPage' ),
		$bodyContent = $( '#bodyContent' ),
		isMobileLayout = window.innerWidth <= 768,
		isArticle = mw.config.get( 'wgIsArticle' );

	// Do nothing when not on an article
	if ( isMainPage || !isArticle ) {
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
