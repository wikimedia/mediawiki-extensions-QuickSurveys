const logEvent = require( './logEvent.js' );

/**
 * @typedef {Object} Audience
 * @property {string[]} [countries] that the survey should be targeted at
 * @property {number} [minEdits] a minimum number of edits the user must have
 *   if undefined there will be no lower bound
 * @property {number} [maxEdits] a maximum number of edits the user must have
 *   if undefined there will be no upper bound
 * @property {boolean} [anons] is the survey targetted to anons/logged in only?
 *   if undefined there will no limit
 * @property {string} [registrationStart] if the survey is targeted by registration
 * date, user had to join on or after this date. Date is in format YYYY-MM-DD
 * @property {string} [registrationEnd] if the survey is targeted by registration
 * date, user had to join on or before this date. Date is in format YYYY-MM-DD
 * @property {number[]} [pageIds]
 * @property {string[]} [userAgent]
 * @property {DateRange} [firstEdit]
 * @property {DateRange} [lastEdit]
 */

/**
 * @typedef Answer
 * @property {string} label
 * @property {string|null|undefined} freeformTextLabel
 */

/**
 * @typedef QuestionDependency
 * @property {string} question
 * @property {string[]|null|undefined} answerIsOneOf
 */

/**
 * @typedef InternalQuestion
 * @property {string} name
 * @property {string} layout
 * @property {string} question
 * @property {string|null|undefined} description
 * @property {boolean|null|undefined} shuffleAnswersDisplay
 * @property {Answer[]} answers
 * @property {QuestionDependency[]|null|undefined} dependsOn
 */

/**
 * @typedef ExternalQuestion
 * @property {string} name
 * @property {string} question
 * @property {string|null|undefined} description
 * @property {string} link
 * @property {string|null|undefined} instanceTokenParameterName
 * @property {string|null|undefined} yesMsg
 * @property {string|null|undefined} noMsg
 */

/**
 * @typedef SurveyDefinition
 * @property {Audience} audience
 * @property {(InternalQuestion|ExternalQuestion)[]} questions
 * @property {number} coverage
 * @property {boolean} isInsecure
 * @property {string} module
 * @property {string} name
 * @property {Object} platforms
 * @property {string} privacyPolicy
 * @property {string|null} additionalInfo
 * @property {string|null} thankYouMessage
 * @property {string} type
 * @property {string|null} embedElementId Embedding location DOM element ID.
 */

/**
 * @typedef {Object} Geo
 * @property {string} country code of the user
 * @property {string} [region] code of the user
 * @property {string} [city] of the user
 * @property {number} [lat] of the user
 * @property {number} [lon] of the user
 */

/**
 * @typedef {Object} DateRange
 * @property {?string} [from]
 * @property {?string} [to]
 */

const hasOwn = Object.hasOwnProperty;

/**
 * Log impression when a survey is seen by the user
 *
 * @param {string} surveySessionToken
 * @param {string} pageviewToken
 * @param {string} surveyCodeName
 */
function logSurveyImpression( surveySessionToken, pageviewToken, surveyCodeName ) {
	const event = {
		surveySessionToken,
		pageviewToken,
		surveyCodeName,
		eventName: 'impression',
		performanceNow: Math.round( mw.now() )
	};

	const editCountBucket = mw.config.get( 'wgUserEditCountBucket' );

	if ( editCountBucket ) {
		event.editCountBucket = editCountBucket;
	}

	logEvent( 'QuickSurveyInitiation', event );
}

/**
 * Get a promise that resolves when half of the element has intersected with the device
 * viewport. If browser doesn't support IntersectionObserver the promise will be rejected.
 *
 * Note well that a promise can only resolve once.
 *
 * @param {HTMLElement} el The element
 * @return {jQuery.Promise}
 */
function getSeenObserver( el ) {
	const result = $.Deferred();

	if ( 'IntersectionObserver' in window ) {
		// Setup the area for observing.
		// By default the root is the viewport which is what we want.
		// See https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API
		const observer = new IntersectionObserver(
			( ( entries ) => {
				const entry = entries && entries[ 0 ];
				if ( entry && entry.isIntersecting ) {
					// If intersecting resolve the promise,
					// and stop observing it to free up resources.
					observer.unobserve( el );
					result.resolve();
				}
			} ),
			{
				threshold: 1
			}
		);
		// This should only ever observe one element given the function returns a promise.
		observer.observe( el );
	} else {
		result.reject();
	}
	return result.promise();
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
 * @param {string|null} embedElementId Embedding location DOM element ID.
 * @param {boolean} isMobileLayout whether the screen is a mobile layout.
 */
function insertPanel( $bodyContent, $panel, embedElementId, isMobileLayout ) {
	let $place;

	if ( embedElementId ) {
		$place = $bodyContent.find( '#' + embedElementId );
	} else if ( isMobileLayout ) {
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
					'.mw-heading'
				].join( ',' )
			)
			.filter( ':not(' + [
				// Minerva
				'.toc-mobile h2',
				// Vector
				'.toc h2'
			].join( ',' ) + ')' )
			.eq( 0 );
		if ( $place.length ) {
			$panel.insertBefore( $place );
		} else {
			// Insert this after the first paragraph (for pages with
			// just one paragraph) or the lead section/content
			// container when no suitable element can be found
			// (empty pages)
			$place = $bodyContent.find( 'p' ).eq( 0 );
			// Do this test separately for cases where no paragraph
			// elements are returned in document order so > div
			// would always come first. See
			// http://blog.jquery.com/2009/02/20/jquery-1-3-2-released/
			if ( !$place.length ) {
				// Note that this will only ever happen if you have
				// an article with no headings and only an empty
				// lead section. We only apply to the first one but
				// technically there should only ever be one.
				// eslint-disable-next-line no-jquery/no-global-selector
				$place = $( '> div' ).eq( 0 );
			}
			$panel.insertAfter( $place );
		}
	}
}

/**
 * Helper method that checks if a date is in a given range. The from and to
 * parameters are inclusive, and when set to null, extend to infinity.
 *
 * @param {string} date the date to range check
 * @param {?string} from start date (inclusive)
 * @param {?string} to end date (inclusive)
 * @return {boolean} return true when the provided date is in the specified range
 */
function dateInRange( date, from, to ) {
	const parsedDate = new Date( date + 'T00:00:00+00:00' );
	const parsedFrom = from ? new Date( from + 'T00:00:00+00:00' ) : new Date( false );
	const parsedTo = to ? new Date( to + 'T23:59:59+0000' ) : new Date();

	return parsedDate >= parsedFrom && parsedDate <= parsedTo;
}

/**
 * Helper method to verify that user registered in given time frame
 * Note: this check is inclusive
 *
 * @param {Object} user User object
 * @param {string} registrationStart date string in YYYY-MM-DD format
 * @param {string} registrationEnd date string in YYYY-MM-DD format
 * @return {boolean} return true when user between given time range
 */
function registrationDateNotInRange( user, registrationStart, registrationEnd ) {
	if ( user.getRegistration() === false ) {
		// we cannot detect user registration date, fail by default
		return true;
	}
	const from = registrationStart ? new Date( registrationStart + 'T00:00:00+00:00' ) : new Date( false );
	const to = registrationEnd ? new Date( registrationEnd + 'T23:59:59+0000' ) : new Date();

	return from > user.getRegistration() || user.getRegistration() > to;
}

/**
 * Check if audience user agent matches survey's target user agent.
 *
 * @param {Array} targetUserAgent
 * @return {boolean} true if user agent matches
 */
function isUsingTargetBrowser( targetUserAgent ) {
	const keywordToRegexMap = {
		KaiOS: /KaiOS[/\s](\d+\.\d+)/i,
		InternetExplorer: /MSIE (\d+\.\d+);/i,
		Chrome: /Chrome[/\s](\d+\.\d+)/i,
		Edge: /Edge\/\d+/i,
		Firefox: /Firefox[/\s](\d+\.\d+)/i,
		Opera: /OPR[/\s](\d+\.\d+)/i,
		Safari: /Safari[/\s](\d+\.\d+)/i
	};
	const targetChrome = targetUserAgent.includes( 'Chrome' );
	let uaMatch = 0;

	// Check each target user agent against the user's user agent.
	targetUserAgent.forEach( ( ua ) => {
		if ( Object.prototype.hasOwnProperty.call( keywordToRegexMap, ua ) &&
			keywordToRegexMap[ ua ].test( navigator.userAgent )
		) {
			++uaMatch;
			// User agent string for Chrome includes Safari, so the simple regex fails to prevent
			// showing a given survey to Chrome when Safari is targeted but Chrome is not.
			if ( ua === 'Safari' && !targetChrome && navigator.userAgent.includes( 'Chrome' ) ) {
				--uaMatch;
			}
		}
	} );
	return !!uaMatch;
}

/**
 * Check if a survey is suitable for the current user
 *
 * @param {Audience} audience
 * @param {Object} user object
 * @param {number|null} editCount of user (null if user is anon)
 * @param {Geo} [geo] geographical information of user (undefined if not known)
 * @param {number} pageId ID of the current page
 * @param {string} firstEdit date of the first edit the user made (YYYY-MM-DD)
 * @param {string} lastEdit date of the last edit the user made (YYYY-MM-DD)
 * @return {boolean}
 */
function isInAudience( audience, user, editCount, geo, pageId, firstEdit, lastEdit ) {
	const hasMinEditAudience = audience.minEdits !== undefined,
		hasMaxEditAudience = audience.maxEdits !== undefined,
		hasCountries = audience.countries !== undefined,
		hasPageIds = audience.pageIds !== undefined,
		hasTarget = audience.userAgent !== undefined && audience.userAgent.length > 0;

	if ( hasPageIds && !audience.pageIds.includes( pageId ) ) {
		return false;
	} else if ( ( audience.registrationStart || audience.registrationEnd ) &&
		registrationDateNotInRange( user, audience.registrationStart,
			audience.registrationEnd ) ) {
		return false;
	} else if (
		audience.firstEdit &&
		( !firstEdit || !dateInRange( firstEdit, audience.firstEdit.from, audience.firstEdit.to ) )
	) {
		return false;
	} else if (
		audience.lastEdit &&
		( !lastEdit || !dateInRange( lastEdit, audience.lastEdit.from, audience.lastEdit.to ) )
	) {
		return false;
	} else if ( audience.anons !== undefined && audience.anons !== user.isAnon() ) {
		return false;
	} else if ( editCount === null && hasMinEditAudience ) {
		return false;
	} else if (
		( hasMinEditAudience && editCount < audience.minEdits ) ||
		( hasMaxEditAudience && editCount > audience.maxEdits )
	) {
		return false;
	}
	geo = geo || { country: '??' };
	if ( hasCountries && !audience.countries.includes( geo.country ) ) {
		return false;
	} else if ( hasTarget && !isUsingTargetBrowser( audience.userAgent ) ) {
		return false;
	}
	return true;
}

/**
 * @param {string} queryString The survey's name or the string "true" to get a random survey
 * @param {Object[]} availableSurveys
 * @return {Object|undefined} Undefined if a survey with this name wasn't found
 */
function getSurveyFromQueryString( queryString, availableSurveys ) {
	if ( queryString === 'true' ) {
		return availableSurveys[ Math.floor( Math.random() * availableSurveys.length ) ];
	}

	// Backwards-compatibility: Drop now unused filter by survey type. Unnecessary because internal
	// and an external surveys with the same name can't exist anyway.
	queryString = queryString.replace( /^(in|ex)ternal-survey-/, '' );
	return availableSurveys.find( ( survey ) => survey.name === queryString );
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
	const a = survey.coverage;
	const control = 1 - survey.coverage;
	const storageId = getSurveyStorageKey( survey );
	let token = getSurveyToken( survey );

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
 * @return {boolean}
 */
function surveyMatchesPlatform( survey, mode ) {
	const platformKey = mode ? 'mobile' : 'desktop',
		platformValue = mode || 'stable';

	return hasOwn.call( survey.platforms, platformKey ) &&
		survey.platforms[ platformKey ].includes( platformValue );
}

/**
 * Logs a survey impression when the survey is observed by user.
 * If the browser does not support IntersectionObserver it will log immediately.
 *
 * @param {HTMLElement} el
 * @param {string} surveySessionToken
 * @param {string} pageviewToken
 * @param {string} surveyName
 */
function reportWhenSeen( el, surveySessionToken, pageviewToken, surveyName ) {
	const done = function () {
		logSurveyImpression( surveySessionToken, pageviewToken, surveyName );
	};
	getSeenObserver( el ).then( done, done );
}

/**
 * Inserts a survey into the page and logs a survey impression.
 * For older browsers, the survey impression will be logged, regardless
 * of whether it is seen.
 *
 * @param {SurveyDefinition} survey
 */
function insertSurvey( survey ) {
	const $panel = $.createSpinner().addClass( 'ext-qs-loader-bar' ),
		// eslint-disable-next-line no-jquery/no-global-selector
		$bodyContent = $( '#bodyContent' ),
		surveySessionToken = mw.user.sessionId() + '-quicksurveys',
		dismissSurvey = function () {
			mw.storage.set( getSurveyStorageKey( survey ), '~' );
		},
		pageviewToken = mw.user.getPageviewToken(),
		isMobileLayout = window.innerWidth <= 768;

	insertPanel( $bodyContent, $panel, survey.embedElementId, isMobileLayout );
	const htmlDirection = document.getElementById( 'firstHeading' ).getAttribute( 'dir' );

	// survey.module contains i18n messages and code to render.
	// We load this asynchronously to avoid loading this all on page load for
	// pages where a survey will never be shown.
	// For example, some surveys are targetted at
	// users with certain edit counts, or certain browser.
	// See SurveyAudience for more information.
	mw.loader.using( [ survey.module ] ).then( ( require ) => {
		const module = require( 'ext.quicksurveys.lib.vue' );
		if ( module ) {
			module.render(
				require( 'vue' ),
				$panel[ 0 ],
				survey,
				dismissSurvey,
				surveySessionToken,
				pageviewToken,
				isMobileLayout,
				htmlDirection,
				logEvent
			).then( ( el ) => {
				// Use the Vue element instead of $panel
				reportWhenSeen( el, surveySessionToken, pageviewToken, survey.name );
			} );
		}
	} );
}

/**
 * Check if a survey matches an element on the current page.
 *
 * @param {string} embedElementId Element to match for survey injection
 * @return {boolean}
 */
function isEmbeddedElementMatched( embedElementId ) {
	return $( '#' + embedElementId ).length > 0;
}

/**
 * Check if user preference has `displayquicksurveys` enabled.
 *
 * @return {boolean}
 */
function isQuickSurveysPrefEnabled() {
	return mw.user.options.get( 'displayquicksurveys' ) === 1;
}

/**
 * Choose and display a survey
 *
 * @param {string|null} forcedSurvey Survey to force display of, if any
 */
function showSurvey( forcedSurvey ) {
	if ( !isQuickSurveysPrefEnabled() ) {
		return;
	}

	const embeddedSurveys = [];
	const availableSurveys = [];
	const enabledSurveys = require( './surveyData.json' );

	if ( forcedSurvey ) {
		// Code path for when …?quicksurvey=… is used in the URL. Notes:
		// - This bypasses all bucket and audience checks.
		// - Only enabled surveys can be accessed like this.
		const enabledSurveyFromQueryString = getSurveyFromQueryString(
			forcedSurvey,
			enabledSurveys
		);
		if ( enabledSurveyFromQueryString ) {
			availableSurveys.push( enabledSurveyFromQueryString );
		}
	} else {
		// Find which surveys are available to the user
		enabledSurveys.forEach( ( enabledSurvey ) => {
			if (
				getSurveyToken( enabledSurvey ) !== '~' &&
				getBucketForSurvey( enabledSurvey ) === 'A' &&
				isInAudience(
					enabledSurvey.audience,
					mw.user,
					mw.config.get( 'wgUserEditCount' ),
					window.Geo,
					mw.config.get( 'wgArticleId' ),
					mw.config.get( 'wgQSUserFirstEditDate' ),
					mw.config.get( 'wgQSUserLastEditDate' )
				) &&
				surveyMatchesPlatform( enabledSurvey, mw.config.get( 'wgMFMode' ) )
			) {
				if ( enabledSurvey.embedElementId ) {
					if ( isEmbeddedElementMatched( enabledSurvey.embedElementId ) ) {
						embeddedSurveys.push( enabledSurvey );
					}
				} else {
					availableSurveys.push( enabledSurvey );
				}
			}
		} );
	}
	if ( embeddedSurveys.length ) {
		// Inject all of the embedded surveys.
		embeddedSurveys.forEach( ( embeddedSurvey ) => {
			insertSurvey( embeddedSurvey );
		} );
	} else if ( availableSurveys.length ) {
		// Get a random available survey
		const survey = availableSurveys[ Math.floor( Math.random() * availableSurveys.length ) ];
		insertSurvey( survey );
	}
}

module.exports = {
	showSurvey
};

if ( window.QUnit ) {
	module.exports.test = {
		getSurveyFromQueryString,
		insertPanel,
		isInAudience,
		surveyMatchesPlatform,
		isQuickSurveysPrefEnabled
	};
}
