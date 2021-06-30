var utils = require( './views/utils.js' );

module.exports = {
	/**
	 * @param {string} name
	 * @param {string} surveySessionToken
	 * @param {string} pageviewToken
	 */
	logInitialized: function ( name, surveySessionToken, pageviewToken ) {
		mw.eventLog.logEvent( 'QuickSurveyInitiation', {
			// eslint-disable-next-line compat/compat
			beaconCapable: !!navigator.sendBeacon,
			surveySessionToken: surveySessionToken,
			pageviewToken: pageviewToken,
			surveyCodeName: name,
			eventName: 'eligible',
			performanceNow: Math.round( mw.now() )
		} );
	},
	/**
	 * Shuffle answers in place
	 *
	 * @param {Array} [answers] answers coming from configuration
	 * @return {Array} shuffled answers
	 */
	shuffleAnswers: function ( answers ) {
		var counter = answers.length,
			i, temp;

		while ( counter > 0 ) {
			i = Math.floor( Math.random() * counter );

			counter--;

			temp = answers[ counter ];
			answers[ counter ] = answers[ i ];
			answers[ i ] = temp;
		}

		return answers;
	},
	/**
	 *
	 * @param {string} name
	 * @param {string} answer
	 * @param {string} surveySessionToken
	 * @param {string} pageviewToken
	 * @param {boolean} isTablet
	 */
	logResponse: function ( name, answer, surveySessionToken, pageviewToken, isTablet ) {
		var
			skin = mw.config.get( 'skin' ),
			// FIXME: remove this when SkinMinervaBeta is renamed to 'minerva-beta'.
			mobileMode = mw.config.get( 'wgMFMode' ),
			event,
			editCountBucket;

		// On mobile differentiate between minerva stable and beta
		// by appending 'beta' to 'minerva'
		if ( skin === 'minerva' && mobileMode === 'beta' ) {
			skin += mobileMode;
		}

		event = {
			namespaceId: mw.config.get( 'wgNamespaceNumber' ),
			surveySessionToken: surveySessionToken,
			pageviewToken: pageviewToken,
			pageId: mw.config.get( 'wgArticleId' ),
			pageTitle: mw.config.get( 'wgPageName' ),
			surveyCodeName: name,
			surveyResponseValue: answer,
			platform: 'web',
			skin: skin,
			isTablet: isTablet,
			userLanguage: mw.config.get( 'wgContentLanguage' ),
			isLoggedIn: !mw.user.isAnon(),
			countryCode: utils.getCountryCode()
		};
		editCountBucket = mw.config.get( 'wgUserEditCountBucket' );
		if ( editCountBucket ) {
			event.editCountBucket = editCountBucket;
		}
		mw.eventLog.logEvent( 'QuickSurveysResponses', event );
	}
};
