( function ( $ ) {
	var survey, token, storageId, $bodyContent, $place,
		$panel = $( '<div class="ext-qs-loader-bar mw-ajax-loader"></div>' ),
		availableSurveys = mw.config.get( 'wgEnabledQuickSurveys' ),
		// https://phabricator.wikimedia.org/T109010
		inSample = false,
		isMainPage = mw.config.get( 'wgIsMainPage' ),
		isArticle = mw.config.get( 'wgIsArticle' );

	mw.extQuickSurveys = mw.extQuickSurveys || {};

	if ( availableSurveys.length ) {
		survey = availableSurveys[ Math.floor( Math.random() * availableSurveys.length ) ];
		storageId = 'ext-quicksurvey-' + survey.name;
		token = mw.storage.get( storageId );

		// local storage is supported in this case as value is not false and when ~ it means it was dismissed
		if ( token !== false && token !== '~' && !isMainPage && isArticle ) {

			if ( !token ) {
				token = mw.user.generateRandomSessionId();
				// given token !== false we can safely run this without exception:
				mw.storage.set( storageId, token );
			}

			if ( inSample || mw.util.getParamValue( 'quicksurvey' ) ) {
				$bodyContent = $( '#bodyContent' );
				$place = $bodyContent.find( 'h1, h2, h3, h4, h5, h6' ).eq( 0 );

				if ( $place.length ) {
					$panel.insertBefore( $place );
				} else {
					$panel.appendTo( $bodyContent );
				}
				// survey.module contains i18n messages
				mw.loader.using( [ 'ext.quicksurveys.views', survey.module ] ).done( function () {
					var panel,
						options = {
							survey: survey,
							templateData: {
								question: mw.msg( survey.question ),
								description: mw.msg( survey.description )
							}
						};

					if ( survey.type === 'internal' ) {
						panel = new mw.extQuickSurveys.views.QuickSurvey( options );
					} else {
						panel = new mw.extQuickSurveys.views.ExternalSurvey( options );
					}
					panel.on( 'dismiss', function () {
						mw.storage.set( storageId, '~' );
					} );
					$panel.replaceWith( panel.$element );
				} );
			}
		}
	}
}( jQuery ) );
