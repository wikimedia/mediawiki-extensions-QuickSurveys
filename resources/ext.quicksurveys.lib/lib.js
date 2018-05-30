( function ( $, mw ) {
	var survey,
		availableSurveys = [],
		$window = $( window ),
		surveyImpressionLogger;

	/**
	 * Log impression when a survey is seen by the user
	 *
	 * @param {jQuery.Object} $el
	 * @param {Object} config - survey config data
	 * @ignore
	 */
	function logSurveyImpression( $el, config ) {
		if ( mw.viewport.isElementInViewport( $el.get( 0 ) ) ) {
			$window.off( 'scroll.quickSurveys', surveyImpressionLogger );

			if ( mw.eventLog ) {
				mw.eventLog.logEvent( 'QuickSurveyInitiation', {
					surveySessionToken: config.surveySessionToken,
					surveyInstanceToken: config.surveyInstanceToken,
					surveyCodeName: config.survey.name,
					eventName: 'impression'
				} );
			}
		}
	}

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
	 * @param {boolean} isMobileLayout whether the screen is a mobile layout.
	 */
	function insertPanel( $bodyContent, $panel, isMobileLayout ) {
		var $place;

		if ( isMobileLayout ) {
			// Find a paragraph in the first section to insert after
			$place = $bodyContent.find( '> div > div' ).eq( 0 ).find( ' > p' ).eq( 0 );
		}

		if ( $place && $place.length ) {
			$panel.insertAfter( $place );
		} else {
			$place = $bodyContent
				.find(
					[
						'.infobox',

						// Account for the Mobile Frontend section wrapper
						// around .thumb.
						'> div > div > .thumb',

						'> div > .thumb',
						'> .thumb',
						'h1, h2, h3, h4, h5, h6'
					].join( ',' )
				)
				.filter( ':not(.toc h2)' )
				.eq( 0 );
			if ( $place.length ) {
				$panel.insertBefore( $place );
			} else {
				// Insert this after the first paragraph (for pages with just one paragraph)
				// or the lead section/content container when no suitable element can be found (empty pages)
				$place = $bodyContent.find( 'p' ).eq( 0 );
				// Do this test separately for cases where no paragraph elements
				// are returned in document order so > div would always come first.
				// See http://blog.jquery.com/2009/02/20/jquery-1-3-2-released/
				if ( !$place.length ) {
					// Note that this will only ever happen if you have an article with no headings
					// and only an empty lead section. We only apply to the first one but technically there
					// should only ever be one.
					$place = $( '> div' ).eq( 0 );
				}
				$panel.insertAfter( $place );
			}
		}
	}

	/**
	 * Check if a survey is valid.
	 * Throws warning when not.
	 * A survey is currently only invalid if it is external and links to a non-https external site.
	 *
	 * @param {Object} survey options
	 * @return {boolean}
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
	 * Return a survey from the available surveys given the survey query string.
	 * If the query string is 'true' return a random survey.
	 * If the query string is either 'internal-survey-XXX' or 'external-survey-XXX' where
	 * 'XXX' is the survey name, then return the survey with the name XXX.
	 * Return null in the remaining cases.
	 *
	 * @param {string} queryString query string
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

	/**
	 * Get the storage key for the given survey.
	 *
	 * @param {Object} survey
	 * @return {string} The survey localStorage key
	 */
	function getSurveyStorageKey( survey ) {
		return 'ext-quicksurvey-' + survey.name.replace( / /g, '-' );
	}

	/**
	 * Get the survey token for the given survey.
	 *
	 * @param {Object} survey
	 * @return {string} The survey token
	 */
	function getSurveyToken( survey ) {
		return mw.storage.get( getSurveyStorageKey( survey ) );
	}

	/**
	 * Get the bucket for the given survey.
	 * Initializes the survey storage with a token
	 *
	 * @param {Object} survey
	 * @return {string} The bucket
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
	 * Check if the current platform matches one of the target platforms of a given survey.
	 *
	 * @param {Object} survey options
	 * @param {string} [mode] the value of wgMFMode
	 * @return {Boolean}
	 */
	function surveyMatchesPlatform( survey, mode ) {
		var platformKey = mode ? 'mobile' : 'desktop',
			platformValue = mode || 'stable';

		return $.inArray( platformKey, Object.keys( survey.platforms ) ) >= 0 &&
			$.inArray( platformValue, survey.platforms[ platformKey ] ) >= 0;
	}

	/**
	 * Show survey
	 *
	 * @param {string} forcedSurvey Survey to force display of, if any
	 */
	function showSurvey( forcedSurvey ) {
		var enabledSurveys = mw.config.get( 'wgEnabledQuickSurveys' ),
			$panel = $( '<div class="ext-qs-loader-bar mw-ajax-loader"></div>' ),
			$bodyContent = $( '#bodyContent' ),
			isMobileLayout = window.innerWidth <= 768;

		// Find which surveys are available to the user
		$( enabledSurveys ).each( function ( i, enabledSurvey ) {
			// Setting the quicksurvey param makes every enabled survey available
			if ( forcedSurvey ) {
				// Setting the param quicksurvey bypasses the bucketing
				enabledSurvey = getSurveyFromQueryString(
					forcedSurvey || '',
					enabledSurveys
				);
				if ( enabledSurvey && isValidSurvey( enabledSurvey ) ) {
					availableSurveys.push( enabledSurvey );
				}
				return false;
			} else if (
				getSurveyToken( enabledSurvey ) !== '~' &&
				getBucketForSurvey( enabledSurvey ) === 'A' &&
				isValidSurvey( enabledSurvey ) &&
				surveyMatchesPlatform( enabledSurvey, mw.config.get( 'wgMFMode' ) )
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
							description: survey.description ? mw.msg( survey.description ) : ''
						},
						surveySessionToken: mw.user.sessionId() + '-quicksurveys',
						surveyInstanceToken: mw.user.stickyRandomId(),
						isMobileLayout: isMobileLayout
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

				surveyImpressionLogger = function () {
					logSurveyImpression( panel.$element, options );
				};
				$window.on( 'scroll.quickSurveys', $.debounce( 250, surveyImpressionLogger ) );
				// maybe the survey is already visible?
				surveyImpressionLogger();
			} );
		}
	}

	mw.extQuickSurveys = {
		surveyMatchesPlatform: surveyMatchesPlatform,
		/* eslint-disable-next-line no-underscore-dangle */
		_insertPanel: insertPanel,
		views: {},
		showSurvey: showSurvey
	};
}( jQuery, mediaWiki ) );
