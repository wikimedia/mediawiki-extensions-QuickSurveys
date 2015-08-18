( function ( $ ) {
	var survey, token, storageId, $bodyContent, $place,
		$panel = $( '<div class="ext-qs-loader-bar mw-ajax-loader"></div>' ),
		availableSurveys = mw.config.get( 'wgEnabledQuickSurveys' ),
		// https://phabricator.wikimedia.org/T109010
		inSample = false,
		isMainPage = mw.config.get( 'wgIsMainPage' ),
		isArticle = mw.config.get( 'wgIsArticle' );

	mw.extQuickSurveys = {
		views: {}
	};

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
				mw.loader.using( 'ext.quicksurveys.views' ).done( function () {
					var panel;
					panel = new mw.extQuickSurveys.views.QuickSurvey( {
							type: survey.type,
							templateData: {
								question: survey.question,
								description: survey.description
							}
						} );
					$panel.replaceWith( panel.$element );
					panel.on( 'dismiss', function () {
						mw.storage.set( storageId, '~' );
					} );
				} );
			}
		}
	}
}( jQuery ) );
