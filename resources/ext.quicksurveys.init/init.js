( function ( $ ) {
	var survey, $bodyContent, $place, randomSurvey, surveyIndex,
		$panel = $( '<div class="ext-qs-loader-bar mw-ajax-loader"></div>' ),
		availableSurveys = mw.config.get( 'wgEnabledQuickSurveys' ),
		isMainPage = mw.config.get( 'wgIsMainPage' ),
		isArticle = mw.config.get( 'wgIsArticle' ),
		sessionId = mw.user.generateRandomSessionId();

	mw.extQuickSurveys = mw.extQuickSurveys || {};

	// Do nothing when not on an article
	if ( isMainPage || !isArticle ) {
		return;
	}

	if ( availableSurveys.length ) {
		// Get a random survey that hasn't been dismissed and in correct bucket
		while ( !survey && availableSurveys.length !== 0 ) {
			surveyIndex = Math.floor( Math.random() * availableSurveys.length );
			randomSurvey = availableSurveys[surveyIndex];
			// Setting the param quicksurvey bypasses the bucketing
			if ( mw.util.getParamValue( 'quicksurvey' ) ) {
				survey = randomSurvey;
				break;
			}
			if (
				getSurveyToken( randomSurvey ) === '~' ||
				getBucketForSurvey( randomSurvey ) !== 'A'
			) {
				availableSurveys.splice( surveyIndex, 1 );
				continue;
			}
			survey = randomSurvey;
		}

		if ( survey ) {
			$bodyContent = $( '.mw-content-ltr, .mw-content-rtl' );
			$place = $bodyContent.find( '> h1, > h2, > h3, > h4, > h5, > h6' ).eq( 0 );

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
					mw.storage.set( getSurveyStorageKey( survey ), '~' );
				} );
				$panel.replaceWith( panel.$element );
			} );
		}
	}

	/**
	 * Get the bucket for the given survey.
	 * Initializes the survey storage with a token
	 *
	 * @returns {String} The bucket
	 */
	function getBucketForSurvey( survey ) {
		var control = 1 - survey.coverage,
			a = survey.coverage,
			storageId = getSurveyStorageKey( survey ),
			token = getSurveyToken( survey );

		if ( !token ) {
			token = sessionId;
			mw.storage.set( storageId, token );
		}
		return mw.experiments.getBucket( {
			name: survey.name,
			enabled: true,
			buckets: {
				control: Number( control ),
				A: Number( a )
			}
		}, token );
	}

	/**
	 * Get the storage key for the given survey.

	 * @returns {String} The survey localstorage key
	 */
	function getSurveyStorageKey( survey ) {
		return 'ext-quicksurvey-' + survey.name.replace( / /g, '-' );
	}

	/**
	 * Get the survey token for the given survey.

	 * @returns {String} The survey token
	 */
	function getSurveyToken( survey ) {
		return mw.storage.get( getSurveyStorageKey( survey ) );
	}

}( jQuery ) );
