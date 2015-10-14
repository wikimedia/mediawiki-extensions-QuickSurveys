// jscs:disable disallowDanglingUnderscores
( function ( $ ) {
	var survey,
		availableSurveys = [];

	/**
	 * Insert the quick survey panel into the article either (in priority order)
	 * (on small screens) after the first paragraph,
	 * before the infobox,
	 * before the first instance of a thumbnail,
	 * before the first instance of a heading
	 * or at the end of the article when no headings nor thumbnails exist.
	 *
	 * @param {jQuery.Object} $bodyContent to add the panel
	 * @param {jQuery.Object} $panel
	 * @param {Boolean} isMobileLayout whether the screen is a mobile layout.
	 */
	function insertPanel( $bodyContent, $panel, isMobileLayout ) {
		var $place;

		if ( isMobileLayout ) {
			$place = $bodyContent.find( '> div > p' ).eq( 0 );
		}

		if ( $place && $place.length ) {
			$panel.insertAfter( $place );
		} else {
			$place = $bodyContent
				// Account for the Mobile Frontend section wrapper around .thumb.
				.find( '.infobox, > div > .thumb, > .thumb, > h1, > h2, > h3, > h4, > h5, > h6' )
				.eq( 0 );
			if ( $place.length ) {
				$panel.insertBefore( $place );
			} else {
				$panel.appendTo( $bodyContent );
			}
		}
	}

	/**
	 * Check if a survey is valid.
	 * Throws warning when not.
	 * A survey is currently only invalid if it is external and links to a non-https external site.
	 *
	 * @param {Object} survey options
	 * @return {Boolean}
	 */
	function isValidSurvey( survey ) {
		if ( survey.type === 'external' ) {
			if ( survey.isInsecure && mw.config.get( 'wgQuickSurveysRequireHttps' ) ) {
				mw.log.warn( 'QuickSurvey with name ' + survey.name + ' has insecure survey link and will not be shown.' );
				return false;
			}
		}
		return true;
	}

	/**
	 * Show survey
	 *
	 * @param {jQuery.Object} $bodyContent to add the panel
	 * @param {Boolean} isMobileLayout whether the screen is a mobile layout.
	 */
	function showSurvey( $bodyContent, isMobileLayout ) {
		var enabledSurveys = mw.config.get( 'wgEnabledQuickSurveys' ),
			$panel = $( '<div class="ext-qs-loader-bar mw-ajax-loader"></div>' );

		// Find which surveys are available to the user
		$( enabledSurveys ).each( function ( i, enabledSurvey ) {
			// Setting the quicksurvey param makes every enabled survey available
			if ( mw.util.getParamValue( 'quicksurvey' ) ) {
				// Setting the param quicksurvey bypasses the bucketing
				enabledSurvey = getSurveyFromQueryString(
					mw.util.getParamValue( 'quicksurvey' ) || '',
					enabledSurveys
				);
				if ( enabledSurvey && isValidSurvey( enabledSurvey ) ) {
					availableSurveys.push( enabledSurvey );
				}
				return false;
			} else if (
				getSurveyToken( enabledSurvey ) !== '~' &&
				getBucketForSurvey( enabledSurvey ) === 'A' &&
				isValidSurvey( enabledSurvey )
			) {
				availableSurveys.push( enabledSurvey );
			}
		} );

		if ( availableSurveys.length ) {
			// Get a random available survey
			survey = availableSurveys[ Math.floor( Math.random() * availableSurveys.length ) ];
			insertPanel( $bodyContent, $panel, isMobileLayout );
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
	 * @return {String} The bucket
	 */
	function getBucketForSurvey( survey ) {
		var control = 1 - survey.coverage,
			a = survey.coverage,
			storageId = getSurveyStorageKey( survey ),
			token = getSurveyToken( survey );

		if ( !token ) {
			// Generate a new token for each survey
			token = mw.user.generateRandomSessionId();
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

	mw.extQuickSurveys = {
		_insertPanel: insertPanel,
		views: {},
		showSurvey: showSurvey
	};
}( jQuery ) );
