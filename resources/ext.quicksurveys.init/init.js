( function ( $ ) {
	var survey, $bodyContent, $place,
		$panel = $( '<div class="ext-qs-loader-bar mw-ajax-loader"></div>' ),
		enabledSurveys = mw.config.get( 'wgEnabledQuickSurveys' ),
		isMainPage = mw.config.get( 'wgIsMainPage' ),
		isArticle = mw.config.get( 'wgIsArticle' ),
		sessionId = mw.user.generateRandomSessionId(),
		availableSurveys = [];

	mw.extQuickSurveys = mw.extQuickSurveys || {};

	// Do nothing when not on an article
	if ( isMainPage || !isArticle ) {
		return;
	}

	// Find which surveys are available to the user
	$( enabledSurveys ).each( function ( i, enabledSurvey ) {
		// Setting the quicksurvey param makes every enabled survey available
		if ( mw.util.getParamValue( 'quicksurvey' ) ) {
			// Setting the param quicksurvey bypasses the bucketing
			enabledSurvey = getSurveyFromQueryString(
				mw.util.getParamValue( 'quicksurvey' ) || '',
				enabledSurveys
			);
			if ( enabledSurvey ) {
				availableSurveys.push( enabledSurvey );
			}
			return false;
		} else if (
			getSurveyToken( enabledSurvey ) !== '~' &&
			getBucketForSurvey( enabledSurvey ) === 'A'
		) {
			availableSurveys.push( enabledSurvey );
		}
	} );

	if ( availableSurveys.length ) {
		// Get a random available survey
		survey = availableSurveys[ Math.floor( Math.random() * availableSurveys.length ) ];
		$bodyContent = $( '.mw-content-ltr, .mw-content-rtl' );
		$place = $bodyContent.find( '> .thumb, > h1, > h2, > h3, > h4, > h5, > h6' ).eq( 0 );

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

	/**
	 * Get the bucket for the given survey.
	 * Initializes the survey storage with a token
	 *
	 * @return {String} The bucket
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

	 * @return {String} The survey localStorage key
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

	/**
	 * Return a survey from the available surveys given the survey query string.
	 * If the query string is 'true' return a random survey.
	 * If the query string is either 'internal-survey-XXX' or 'external-survey-XXX' where
	 * 'XXX' is the survey name, then return the survey with the name XXX.
	 * Return null in the remaining cases.
	 *
	 * @param {String} queryString query string
	 * @param {Array} availableSurveys array of survey objects
	 * @return {Object|null} Survey object or null
	 */
	function getSurveyFromQueryString( queryString, availableSurveys ) {
		var surveyIndex,
			surveyType,
			surveyName,
			survey = null;

		// true returns a random survey
		if ( queryString === 'true' ) {
			surveyIndex = Math.floor( Math.random() * availableSurveys.length );
			survey = availableSurveys[ surveyIndex ];
		} else if ( queryString.indexOf( 'internal-survey-' ) === 0 ) {
			surveyType = 'internal';
		} else if ( queryString.indexOf( 'external-survey-' ) === 0 ) {
			surveyType = 'external';
		}

		if ( surveyType ) {
			surveyName = queryString.split( '-' ).slice( 2 ).join( '-' );
			availableSurveys = $.grep( availableSurveys, function ( survey ) {
				return survey.name === surveyName && survey.type === surveyType;
			} );
			if ( availableSurveys.length ) {
				survey = availableSurveys[ 0 ];
			}
		}

		return survey;
	}

}( jQuery ) );
