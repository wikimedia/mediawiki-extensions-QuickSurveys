const utils = require( './utils.js' );

module.exports = {
	/**
	 * @param {string} name
	 * @param {string} question
	 * @param {Object} answers
	 * @param {string} surveySessionToken
	 * @param {string} pageviewToken
	 * @param {boolean} isTablet
	 * @return {Array} of data to be passed to logger
	 */
	logResponseData(
		name,
		question,
		answers,
		surveySessionToken,
		pageviewToken,
		isTablet
	) {
		let skin = mw.config.get( 'skin' );
		// FIXME: remove this when SkinMinervaBeta is renamed to 'minerva-beta'.
		const mobileMode = mw.config.get( 'wgMFMode' );

		// On mobile differentiate between minerva stable and beta
		// by appending 'beta' to 'minerva'
		if ( skin === 'minerva' && mobileMode === 'beta' ) {
			skin += mobileMode;
		}

		const surveyAnswers = Object.keys( answers );

		// Filter out freeform text answers that are blank.
		const surveyResponseFreeText = surveyAnswers.reduce( ( previous, current ) => {
			const value = answers[ current ];
			if ( value ) {
				previous[ current ] = value;
			}
			return previous;
		}, {} );

		const event = {
			namespaceId: mw.config.get( 'wgNamespaceNumber' ),
			surveySessionToken,
			pageviewToken,
			pageId: mw.config.get( 'wgArticleId' ),
			pageTitle: mw.config.get( 'wgPageName' ),
			surveyCodeName: name,
			platform: 'web',
			skin,
			isTablet,
			userLanguage: mw.config.get( 'wgContentLanguage' ),
			isLoggedIn: !mw.user.isAnon(),
			countryCode: utils.getCountryCode(),
			// Deprecated, but required.
			surveyResponseValue: surveyAnswers
				.map( ( answer ) => encodeURIComponent( answer ) )
				.join( ',' ),
			surveyQuestionLabel: question,
			surveyAnswers: Array.from( surveyAnswers ),
			surveyResponseFreeText
		};
		const editCountBucket = mw.config.get( 'wgUserEditCountBucket' );
		if ( editCountBucket ) {
			event.editCountBucket = editCountBucket;
		}
		return event;
	}
};
